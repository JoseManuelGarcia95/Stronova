<?php

namespace App\Controller;

use App\Entity\RutinaEjercicio;
use App\Repository\RutinaEjercicioRepository;
use App\Repository\RutinaRepository;
use App\Repository\EjercicioRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/rutina-ejercicios')]
class RutinaEjercicioController extends ApiController
{
    private $rutinaEjercicioRepository;
    private $rutinaRepository;
    private $ejercicioRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        RutinaEjercicioRepository $rutinaEjercicioRepository,
        RutinaRepository $rutinaRepository,
        EjercicioRepository $ejercicioRepository
    ) {
        parent::__construct($entityManager, $serializer, $validator);
        $this->rutinaEjercicioRepository = $rutinaEjercicioRepository;
        $this->rutinaRepository = $rutinaRepository;
        $this->ejercicioRepository = $ejercicioRepository;
    }

    // Obtener id de las rutinas/ejercicios
    #[Route('', name: 'app_rutina_ejercicios_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $rutinaEjercicios = $this->rutinaEjercicioRepository->findAll();
        $data = $this->serializer->serialize($rutinaEjercicios, 'json', ['groups' => 'rutina_ejercicio:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Obtener todos las rutinas/ejercicios
    #[Route('/{id}', name: 'app_rutina_ejercicios_show', methods: ['GET'])]
    public function show(RutinaEjercicio $rutinaEjercicio): JsonResponse
    {
        $data = $this->serializer->serialize($rutinaEjercicio, 'json', ['groups' => 'rutina_ejercicio:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Crear una rutina/ejercicio
    #[Route('', name: 'app_rutina_ejercicios_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $rutinaEjercicio = new RutinaEjercicio();

        // Relaciones
        if (isset($data['rutina_id'])) {
            $rutina = $this->rutinaRepository->find($data['rutina_id']);
            if (!$rutina) {
                return new JsonResponse(['error' => 'Rutina no encontrada'], Response::HTTP_NOT_FOUND);
            }
            $rutinaEjercicio->setRutina($rutina);
        }

        if (isset($data['ejercicio_id'])) {
            $ejercicio = $this->ejercicioRepository->find($data['ejercicio_id']);
            if (!$ejercicio) {
                return new JsonResponse(['error' => 'Ejercicio no encontrado'], Response::HTTP_NOT_FOUND);
            }
            $rutinaEjercicio->setEjercicio($ejercicio);
        }

        // Establecer atributos
        $rutinaEjercicio->setSeries($data['series'] ?? 0);
        $rutinaEjercicio->setRepeticiones($data['repeticiones'] ?? 0);

        if(isset($data['descanso_segundos'])) {
            $rutinaEjercicio->setDescansoSegundos($data['descanso_segundos']);
        }

        if(isset($data['orden'])) {
            $rutinaEjercicio->setOrden($data['orden']);
        }

        if(isset($data['notas'])) {
            $rutinaEjercicio->setNotas($data['notas']);
        }

        // Validar
        $errors = $this->validator->validate($rutinaEjercicio);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }

        // Guardar BBDD
        $this->entityManager->persist($rutinaEjercicio);
        $this->entityManager->flush();

        // Serializar respuesta
        $data = $this->serializer->serialize($rutinaEjercicio, 'json', ['groups' => 'rutina_ejercicio:read']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    // Actualizar una rutina/ejercicio
    #[Route('/{id}', name: 'app_rutina_ejercicios_update', methods: ['PUT'])]
    public function update(Request $request, RutinaEjercicio $rutinaEjercicio): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Relaciones
        if (isset($data['rutina_id'])) {
            $rutina = $this->rutinaRepository->find($data['rutina_id']);
            if (!$rutina) {
                return new JsonResponse(['error' => 'Rutina no encontrada'], Response::HTTP_NOT_FOUND);
            }
            $rutinaEjercicio->setRutina($rutina);
        }

        if (isset($data['ejercicio_id'])) {
            $ejercicio = $this->ejercicioRepository->find($data['ejercicio_id']);
            if (!$ejercicio) {
                return new JsonResponse(['error' => 'Ejercicio no encontrado'], Response::HTTP_NOT_FOUND);
            }
            $rutinaEjercicio->setEjercicio($ejercicio);
        }

        // Actualizar los atributos
        if (isset($data['series'])) {
            $rutinaEjercicio->setSeries($data['series']);
        }

        if (isset($data['repeticiones'])) {
            $rutinaEjercicio->setRepeticiones($data['repeticiones']);
        }

        if(isset($data['descanso_segundos'])) {
            $rutinaEjercicio->setDescansoSegundos($data['descanso_segundos']);
        }

        if(isset($data['orden'])) {
            $rutinaEjercicio->setOrden($data['orden']);
        }

        if(isset($data['notas'])) {
            $rutinaEjercicio->setNotas($data['notas']);
        }

        // Validar
        $errors = $this->validator->validate($rutinaEjercicio);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }

        // Guardar BBDD
        $this->entityManager->flush();

        // Serializar respuesta
        $data = $this->serializer->serialize($rutinaEjercicio, 'json', ['groups' => 'rutina_ejercicio:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Eliminar una rutina/ejercicio
    #[Route('/{id}', name: 'app_rutina_ejercicios_delete', methods: ['DELETE'])]
    public function delete(RutinaEjercicio $rutinaEjercicio): JsonResponse
    {
        $this->entityManager->remove($rutinaEjercicio);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // Buscar por nombre de ejercicio
    #[Route('/ejercicio/{nombreEjercicio}', name: 'app_rutina_ejercicios_por_nombre_ejercicios', methods: ['GET'])]
    public function buscarPorNombreEjercicio(string $nombreEjercicio): JsonResponse
    {
        $rutinaEjercicios = $this->rutinaEjercicioRepository->findByNombreEjercicio($nombreEjercicio);
        $data = $this->serializer->serialize($rutinaEjercicios, 'json', ['groups' => 'rutina_ejercicio:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Buscar por nombre de rutina
    #[Route('/rutina-nombre/{nombreRutina}', name: 'app_rutina_ejercicios_por_nombre_rutina', methods: ['GET'])]
    public function buscarPorNombreRutina(string $nombreRutina): JsonResponse
    {
        $rutinaEjercicios = $this->rutinaEjercicioRepository->findByNombreRutina($nombreRutina);
        $data = $this->serializer->serialize($rutinaEjercicios, 'json', ['groups' => 'rutina_ejercicio:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}