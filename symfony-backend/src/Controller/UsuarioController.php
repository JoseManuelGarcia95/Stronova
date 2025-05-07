<?php 

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/usuarios')]
class UsuarioController extends ApiController
{
    private $usuarioRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UsuarioRepository $usuarioRepository
    ) {
        parent::__construct($entityManager, $serializer, $validator);
        $this->usuarioRepository = $usuarioRepository;
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
        $usuarios = $this->usuarioRepository->findByApellidos(['apellidos' => $apellidos]);
        $data = $this->serializer->serialize($usuarios, 'json', ['groups' => 'usuario:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Obtener usuario por objetivo
    #[Route('/buscar/objetivo/{objetivo}', name: 'app_usuarios_buscar_objetivo', methods: ['GET'])]
    public function buscarPorObjetivo(string $objetivo): JsonResponse
    {
        $usuarios = $this->usuarioRepository->findByObjetivo(['objetivo' => $objetivo]);
        $data = $this->serializer->serialize($usuarios, 'json', ['groups' => 'usuario:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Obtener usuarios sin entrenador
    #[Route('/sin-entrenador', name: 'app_usuarios_sin_entrenador', methods: ['GET'])]
    public function usuariosSinEntrenador(): JsonResponse
    {
        $usuarios = $this->usuarioRepository->findSinEntrenador();
        $data = $this->serializer->serialize($usuarios, 'json', ['groups' => 'usuario:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}