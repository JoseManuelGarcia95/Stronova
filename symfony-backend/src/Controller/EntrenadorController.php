<?php

namespace App\Controller;

use App\Entity\Entrenador;
use App\Repository\EntrenadorRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/entrenadores')]
class EntrenadorController extends ApiController
{
    private $entrenadorRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EntrenadorRepository $entrenadorRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct($entityManager, $serializer, $validator);
        $this->entrenadorRepository = $entrenadorRepository;
        $this->passwordHasher = $passwordHasher;
    }

    // Obtener entrenador por id
    #[Route('', name: 'app_entrenadores_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $entrenadores = $this->entrenadorRepository->findAll();
        $data = $this->serializer->serialize($entrenadores, 'json', ['groups' => 'entrenador:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Obtener todos los entrenadores
    #[Route('/{id}', name: 'app_entrenadores_show', methods: ['GET'])]
    public function show(Entrenador $entrenador): JsonResponse
    {
        $data = $this->serializer->serialize($entrenador, 'json', ['groups' => 'entrenador:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Crear entrenador
    #[Route('', name: 'app_entrenadores_create', methods: ['POST'])]
    public function create(Request $request): Jsonresponse
    {
        $data = json_decode($request->getContent(), true);

        $entrenador = new Entrenador();
        $entrenador->setNombre($data['nombre'] ?? null);
        $entrenador->setApellidos($data['apellidos'] ?? null);
        $entrenador->setEmail($data['email'] ?? null);
        
        if (isset($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $entrenador, 
                $data['password']
            );
            $entrenador->setPassword($hashedPassword);
        }

        if (isset($data['especialidad'])) {
            $entrenador->setEspecialidad($data['especialidad']);
        }
        // Entrenador nuevo, se crea con 0 usuarios
        $entrenador->setClientesActivos(0);

        $errors = $this->validator->validate($entrenador);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }

        $this->entityManager->persist($entrenador);
        $this->entityManager->flush();

        $data = $this->serializer->serialize($entrenador, 'json', ['groups' => 'entrenador:read']);
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    // Actualizar entrenador
    #[Route('/{id}', name: 'app_entrenadores_update', methods: ['PUT'])]
    public function update(Request $request, Entrenador $entrenador): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['nombre'])) {
            $entrenador->setNombre($data['nombre']);
        }
        if (isset($data['apellidos'])) {
            $entrenador->setApellidos($data['apellidos']);
        }
        if (isset($data['email'])) {
            $entrenador->setEmail($data['email']);
        }

        if (isset($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $entrenador, 
                $data['password']
            );
            $entrenador->setPassword($hashedPassword);
        }

        if (isset($data['especialidad'])) {
            $entrenador->setEspecialidad($data['especialidad']);
        }

        $errors = $this->validator->validate($entrenador);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }

        $this->entityManager->flush();

        $data = $this->serializer->serialize($entrenador, 'json', ['groups' => 'entrenador:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Borrar entrenador
    #[Route('/{id}', name: 'app_entrenadores_delete', methods: ['DELETE'])]
    public function delete(Entrenador $entrenador): JsonResponse
    {
        $this->entityManager->remove($entrenador);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // Obtener entrenador por apellidos
    #[Route('/buscar/apellidos/{apellidos}', name: 'app_entrenadores_buscar_apellidos', methods: ['GET'])]
    public function buscarPorApellidos(string $apellidos): JsonResponse
    {
        $entrenadores = $this->entrenadorRepository->findByApellidos($apellidos);
        $data = $this->serializer->serialize($entrenadores, 'json', ['groups' => 'entrenador:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Obtener entrenador por especialidad
    #[Route('/buscar/especialidad/{especialidad}', name: 'app_entrenadores_buscar_especialidad', methods: ['GET'])]
    public function buscarPorEspecialidad(string $especialidad): JsonResponse
    {
        $entrenadores = $this->entrenadorRepository->findByEspecialidad($especialidad);
        $data = $this->serializer->serialize($entrenadores, 'json', ['groups' => 'entrenador:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Asignar usuario a entrenador
    #[Route('/{id}/asignar-usuario/{usuarioId}', name: 'app_entrenadores_asignar_usuario', methods: ['PUT'])]
    public function asignarUsuario(Entrenador $entrenador, int $usuarioId): JsonResponse
    {
        // Buscar usuario por ID
        $usuario = $this->entityManager->getRepository(Usuario::class)->find($usuarioId);

        if (!$usuario) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        // Verificar si el usuario ya está asignado al entrenador
        $entrenadorActual = $usuario->getEntrenador();

        // Asignar el usuario al entrenador
        $entrenador->addUsuario($usuario);

        // Actualizar el número de clientes activos del entrenador
        if ($entrenadorActual && $entrenadorActual !== $entrenador){
            $entrenadorActual->actualizarContadorClientes();
        }

        $this->entityManager->flush();

        $data = $this->serializer->serialize($entrenador, 'json', ['groups' => 'entrenador:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Desasignar usuario de entrenador
    #[Route('/{id}/desasignar-usuario/{usuarioId}', name: 'app_entrenadores_desasignar_usuario', methods: ['PUT'])]
    public function desasignarUsuario(Entrenador $entrenador, int $usuarioId): JsonResponse
    {
        // Buscar usuario por ID
        $usuario = $this->entityManager->getRepository(Usuario::class)->find($usuarioId);
        if (!$usuario) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }
        // Verificar si el usuario ya está asignado al entrenador
        if ($usuario->getEntrenador() !== $entrenador) {
            return new JsonResponse(['error' => 'El usuario no está asignado a este entrenador'], Response::HTTP_BAD_REQUEST);
        }

        // Desasignar el usuario del entrenador
        $entrenador->removeUsuario($usuario);

        $this->entityManager->flush();

        $data = $this->serializer->serialize($entrenador, 'json', ['groups' => 'entrenador:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}