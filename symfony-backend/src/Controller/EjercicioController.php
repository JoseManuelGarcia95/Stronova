<?php

namespace App\Controller;

use App\Entity\Ejercicio;
use App\Repository\EjercicioRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/ejercicios')]
class EjercicioController extends ApiController
{
    private $ejercicioRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EjercicioRepository $ejercicioRepository
    ) {
        parent::__construct($entityManager, $serializer, $validator);
        $this->ejercicioRepository = $ejercicioRepository;
    }

    // Obtener ejercicios por id 
    #[Route('', name: 'app_ejercicios_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $ejercicios = $this->ejercicioRepository->findAll();
        $data = $this->serializer->serialize($ejercicios, 'json', ['groups' => 'ejercicio:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Obtener todos los ejercicios
    #[Route('/{id}', name: 'app_ejercicios_show', methods: ['GET'])]
    public function show(Ejercicio $ejercicio): JsonResponse
    {
        $data = $this->serializer->serialize($ejercicio, 'json', ['groups' => 'ejercicio:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Crear un nuevo ejercicio
    #[Route('', name: 'app_ejercicios_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $ejercicio = new Ejercicio();
        $ejercicio->setNombre($data['nombre'] ?? null);
        $ejercicio->setDescripcion($data['descripcion'] ?? null);
        $ejercicio->setDificultad($data['dificultad'] ?? null);
        $ejercicio->setCategoria($data['categoria'] ?? null);

        // Validar el ejercicio
        $errors = $this->validator->validate($ejercicio);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }

        // Guardar el ejercicio en la base de datos
        $this->entityManager->persist($ejercicio);
        $this->entityManager->flush();

        $data = $this->serializer->serialize($ejercicio, 'json', ['groups' => 'ejercicio:read']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    // Actualizar un ejercicio existente
    #[Route('/{id}', name: 'app_ejercicios_update', methods: ['PUT'])]
    public function update(Request $request, Ejercicio $ejercicio): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['nombre'])) {
            $ejercicio->setNombre($data['nombre']);
        }
        if (isset($data['descripcion'])) {
            $ejercicio->setDescripcion($data['descripcion']);
        }
        if (isset($data['dificultad'])) {
            $ejercicio->setDificultad($data['dificultad']);
        }
        if (isset($data['categoria'])) {
            $ejercicio->setCategoria($data['categoria']);
        }

        $errors = $this->validator->validate($ejercicio);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }

        // Guardar los cambios en la base de datos
        $this->entityManager->flush();
        $data = $this->serializer->serialize($ejercicio, 'json', ['groups' => 'ejercicio:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Eliminar un ejercicio
    #[Route('/{id}', name: 'app_ejercicios_delete', methods: ['DELETE'])]
    public function delete(Ejercicio $ejercicio): JsonResponse
    {
        $this->entityManager->remove($ejercicio);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // Obtener ejercicios por nombre
    #[Route('/buscar/nombre/{nombre}', name: 'app_ejercicios_buscar_nombre', methods: ['GET'])]
    public function buscarPorNombre(string $nombre): JsonResponse
    {
        $ejercicios = $this->ejercicioRepository->findByNombre($nombre);
        $data = $this->serializer->serialize($ejercicios, 'json', ['groups' => 'ejercicio:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Obtener ejercicios por categoría
    #[Route('/buscar/categoria/{categoria}', name: 'app_ejercicios_buscar_categoria', methods: ['GET'])]
    public function buscarPorCategoria(string $categoria): JsonResponse
    {
        $ejercicios = $this->ejercicioRepository->findByCategoria($categoria);
        $data = $this->serializer->serialize($ejercicios, 'json', ['groups' => 'ejercicio:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}