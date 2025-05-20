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
}