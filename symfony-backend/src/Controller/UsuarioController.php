<?php 

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/usuarios')]
class UsuarioController extends ApiController
{
    private $usuarioRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UsuarioRepository $usuarioRepository,
        UserPasswordHasherInterface $passwordHasher

    ) {
        parent::__construct($entityManager, $serializer, $validator);
        $this->usuarioRepository = $usuarioRepository;
        $this->passwordHasher = $passwordHasher;
    }

    // Obtener usuario por id
    #[Route('', name: 'app_usuarios_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $usuarios = $this->usuarioRepository->findAll();
        $data = $this->serializer->serialize($usuarios, 'json', ['groups' => 'usuario:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Obtener usuarios

    #[Route('/{id}', name: 'app_usuarios_show', methods: ['GET'])]
    public function show(Usuario $usuario): JsonResponse
    {
        $data = $this->serializer->serialize($usuario, 'json', ['groups' => 'usuario:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Crear usuario
    #[Route('', name: 'app_usuarios_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $usuario = new Usuario();
        $usuario->setNombre($data['nombre'] ?? null);
        $usuario->setApellidos($data['apellidos'] ?? null);
        $usuario->setEmail($data['email'] ?? null);
        $usuario->setGenero($data['genero'] ?? null);

        if (isset($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $usuario, 
                $data['password']
            );
            $usuario->setPassword($hashedPassword);
        }

        if (isset($data['altura'])) {
            $usuario->setAltura($data['altura']);
        }
        if (isset($data['peso_inicial'])) {
            $usuario->setPesoInicial($data['peso_inicial']);
        }
        if (isset($data['lesiones'])) {
            $usuario->setLesiones($data['lesiones']);
        }   
        if (isset($data['objetivo'])) {
            $usuario->setObjetivo($data['objetivo']);
        }
        $errors = $this->validator->validate($usuario);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }
        $this->entityManager->persist($usuario);
        $this->entityManager->flush();

        $data = $this->serializer->serialize($usuario, 'json', ['groups' => 'usuario:read']);
        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    // Borrar usuario
    #[Route('/{id}', name: 'app_usuarios_delete', methods: ['DELETE'])]
    public function delete(Usuario $usuario): JsonResponse
    {
        $this->entityManager->remove($usuario);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // Obtener usuario por apellidos

    #[Route('/buscar/apellidos/{apellidos}', name: 'app_usuarios_buscar_apellidos', methods: ['GET'])]
    public function buscarPorApellidos(string $apellidos): JsonResponse
    {
        $usuarios = $this->usuarioRepository->findByApellidos($apellidos);
        $data = $this->serializer->serialize($usuarios, 'json', ['groups' => 'usuario:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Obtener usuario por objetivo
    #[Route('/buscar/objetivo/{objetivo}', name: 'app_usuarios_buscar_objetivo', methods: ['GET'])]
    public function buscarPorObjetivo(string $objetivo): JsonResponse
    {
        $usuarios = $this->usuarioRepository->findByObjetivo($objetivo);
        $data = $this->serializer->serialize($usuarios, 'json', ['groups' => 'usuario:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Modificar usuario
    #[Route('/{id}', name: 'app_usuarios_update', methods: ['PUT'])]
    public function update(Request $request, Usuario $usuario): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['nombre'])) {
            $usuario->setNombre($data['nombre']);
        }
        if (isset($data['apellidos'])) {
            $usuario->setApellidos($data['apellidos']);
        }
        if (isset($data['email'])) {
            $usuario->setEmail($data['email']);
        }
        if (isset($data['genero'])) {
            $usuario->setGenero($data['genero']);
        }
        if (isset($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $usuario, 
                $data['password']
            );
            $usuario->setPassword($hashedPassword);
        }
        if (isset($data['altura'])) {
            $usuario->setAltura($data['altura']);
        }
        if (isset($data['peso_inicial'])) {
            $usuario->setPesoInicial($data['peso_inicial']);
        }
        if (isset($data['lesiones'])) {
            $usuario->setLesiones($data['lesiones']);
        }
        if (isset($data['objetivo'])) {
            $usuario->setObjetivo($data['objetivo']);
        }

        $errors = $this->validator->validate($usuario);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }

        $this->entityManager->flush();

        $data = $this->serializer->serialize($usuario, 'json', ['groups' => 'usuario:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Obtener usuario sin entrenador
    #[Route('/sin-entrenador', name: 'app_usuarios_sin_entrenador', methods: ['GET'])]
    public function usuariosSinEntrenador(): JsonResponse
    {
        $usuariosSinEntrenador = $this->entityManager->getRepository(Usuario::class)
            ->createQueryBuilder('u')
            ->where('u.entrenador IS NULL')
            ->getQuery()
            ->getResult();

        $data = $this->serializer->serialize($usuariosSinEntrenador, 'json', ['groups' => 'usuario:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}