<?php

namespace App\Controller;

use App\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/card', name: 'api_card_')]
#[OA\Tag(name: 'Card', description: 'Routes for all about cards')]
class ApiCardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route('/all', name: 'List all cards', methods: ['GET'])]
    #[OA\Get(description: 'Return all cards in the database')]
    #[OA\Response(response: 200, description: 'List all cards')]
    public function cardAll(Request $request): Response
    {
        $this->logger->info('API call: List all cards');
        $startTime = microtime(true);
    
        try {
            $setCode = $request->query->get('setCode');
            $page = max(1, (int) $request->query->get('page', 1));
            $limit = 100;
            $offset = ($page - 1) * $limit;

            if ($setCode) {
                $cards = $this->entityManager->getRepository(Card::class)->findBy(
                    ['setCode' => $setCode],
                    null,
                    $limit,
                    $offset
                );
            } else {
                $queryBuilder = $this->entityManager->getRepository(Card::class)->createQueryBuilder('c');
                $cards = $queryBuilder->setMaxResults($limit)
                    ->setFirstResult($offset)
                    ->getQuery()
                    ->getResult();
            }
    
            $duration = microtime(true) - $startTime;
            $this->logger->info('API call completed: List all cards', ['duration' => $duration]);
    
            return $this->json($cards);
        } catch (\Exception $e) {
            $this->logger->error('Error listing all cards', ['exception' => $e->getMessage()]);
            return $this->json(['error' => 'An error occurred while listing all cards'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/search/{name}', name: 'Search cards by name', methods: ['GET'])]
    #[OA\Parameter(name: 'name', description: 'Name of the card', in: 'path', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'setCode', description: 'Set code of the card', in: 'query', required: false, schema: new OA\Schema(type: 'string'))]
    #[OA\Get(description: 'Search cards by name')]
    #[OA\Response(response: 200, description: 'Search results')]
    #[OA\Response(response: 404, description: 'No cards found')]
    public function searchCardsByName(string $name, Request $request): Response
    {
        $this->logger->info('API call: Search cards by name', ['name' => $name]);
        $startTime = microtime(true);

        $setCode = $request->query->get('setCode');
        $queryBuilder = $this->entityManager->getRepository(Card::class)->createQueryBuilder('c')
            ->where('c.name LIKE :name')
            ->setParameter('name', '%' . $name . '%');

        if ($setCode) {
            $queryBuilder->andWhere('c.setCode = :setCode')
                ->setParameter('setCode', $setCode);
        }

        $cards = $queryBuilder->setMaxResults(20)
            ->getQuery()
            ->getResult();

        if (!$cards) {
            $this->logger->error('No cards found', ['name' => $name]);
            return $this->json(['error' => 'No cards found'], 404);
        }

        $duration = microtime(true) - $startTime;
        $this->logger->info('API call completed: Search cards by name', ['name' => $name, 'duration' => $duration]);

        return $this->json($cards);
    }

    #[Route('/setcodes', name: 'List all set codes', methods: ['GET'])]
    #[OA\Get(description: 'Return all set codes in the database')]
    #[OA\Response(response: 200, description: 'List all set codes')]
    public function listSetCodes(): Response
    {
        $this->logger->info('API call: List all set codes');
        $startTime = microtime(true);
    
        $setCodes = $this->entityManager->getRepository(Card::class)->createQueryBuilder('c')
            ->select('DISTINCT c.setCode')
            ->getQuery()
            ->getResult();
    
        $duration = microtime(true) - $startTime;
        $this->logger->info('API call completed: List all set codes', ['duration' => $duration]);
    
        return $this->json($setCodes);
    }

    #[Route('/{uuid}', name: 'Show card', methods: ['GET'])]
    #[OA\Parameter(name: 'uuid', description: 'UUID of the card', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Get(description: 'Get a card by UUID')]
    #[OA\Response(response: 200, description: 'Show card')]
    #[OA\Response(response: 404, description: 'Card not found')]
    public function cardShow(string $uuid): Response
    {
        $this->logger->info('API call: Show card', ['uuid' => $uuid]);
        $startTime = microtime(true);

        $card = $this->entityManager->getRepository(Card::class)->findOneBy(['uuid' => $uuid]);

        if (!$card) {
            $this->logger->error('Card not found', ['uuid' => $uuid]);
            return $this->json(['error' => 'Card not found'], 404);
        }

        $duration = microtime(true) - $startTime;
        $this->logger->info('API call completed: Show card', ['uuid' => $uuid, 'duration' => $duration]);

        return $this->json($card);
    }
}
