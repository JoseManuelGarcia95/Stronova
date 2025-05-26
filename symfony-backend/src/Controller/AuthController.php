<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private ValidatorInterface $validator;
    private SerializerInterface $serializer;
    private UsuarioRepository $usuarioRepository;
    private JWTTokenManagerInterface $JWTManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        UsuarioRepository $usuarioRepository,
        JWTTokenManagerInterface $JWTManager
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->usuarioRepository = $usuarioRepository;
        $this->JWTManager = $JWTManager;
    }

    /**
     * @Route("/api/register", name="api_register", methods={"POST"})
     */
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validar datos requeridos
        if (!isset($data['nombre']) || !isset($data['apellidos']) || !isset($data['email']) || 
            !isset($data['password']) || !isset($data['genero'])) {
            return $this->json([
                'message' => 'Faltan campos requeridos (nombre, apellidos, email, password, genero)'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verificar si el email ya existe
        if ($this->usuarioRepository->findOneBy(['email' => $data['email']])) {
            return $this->json([
                'message' => 'Este email ya está registrado'
            ], Response::HTTP_CONFLICT);
        }

        // Crear nuevo usuario
        $usuario = new Usuario();
        $usuario->setNombre($data['nombre']);
        $usuario->setApellidos($data['apellidos']);
        $usuario->setEmail($data['email']);
        $usuario->setGenero($data['genero']);

        // Hashear la contraseña
        $hashedPassword = $this->passwordHasher->hashPassword($usuario, $data['password']);
        $usuario->setPassword($hashedPassword);

        // Agregar datos opcionales si existen
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

        // Validar entidad
        $errors = $this->validator->validate($usuario);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return $this->json([
                'message' => 'Error de validación',
                'errors' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }

        // Guardar usuario en la base de datos
        $this->entityManager->persist($usuario);
        $this->entityManager->flush();

        // Generar token JWT
        $token = $this->JWTManager->create($usuario);

        // Serializar usuario para respuesta (excluyendo la contraseña)
        $userData = $this->serializer->serialize($usuario, 'json', ['groups' => 'usuario:read']);
        $userData = json_decode($userData, true);

        // Devolver respuesta con token y datos del usuario
        return $this->json([
            'message' => 'Usuario registrado correctamente',
            'token' => $token,
            'user' => $userData
        ], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/login", name="api_login", methods={"POST"})
     */
    public function login(Request $request): JsonResponse 
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email']) || !isset($data['password'])){
            return $this->json([
                'message' => 'Email y contraseña son requeridos'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Buscar primero en usuario
        $usuario = $this->usuarioRepository->findOneBy(['email'=> $data['email']]);
        $userType = 'client';
        $user = $usuario;

        // Si no es usuario, buscar en entrenadores
        if (!$usuario) {
            $entrenadorRepository = $this->entityManager->getRepository(\App\Entity\Entrenador::class);
            $entrenador = $entrenadorRepository->findOneBy(['email' => $data['email']]);

            if ($entrenador) {
                $userType = 'trainer';
                $user = $entrenador;
            }
        }

        if (!$user) {
            return $this->json([
                'message' => 'Credenciales incorrectas'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $data['password'])){
            return $this->json([
                'message' => 'Credenciales incorrectas'
            ], Response::HTTP_UNAUTHORIZED);
        }
        $token = $this->JWTManager->create($user);

        // Datos básicos del usuario
        $userData = [
            'id' => $user->getId(),
            'nombre' => $user->getNombre(),
            'apellidos' => $user->getApellidos(),
            'email' => $user->getEmail(),
            'type' => $user->getType()
        ];

        // Agregar campos especificos según el tipo de usuario
        if ($userType === 'client') {
            $userData['entrenador_id'] = $user->getEntrenador() ? $user->getEntrenador()->getId() : null;
        } else {
            $userData['especialidad'] = $user->getEspecialidad();
            $userData['clientes_activos'] = $user->getClientesActivos();
        }

        return $this->json([
            'message' => 'Login exitoso',
            'token' => $token,
            'user' => $userData
        ]);
    }
}