<?php   

namespace App\Controller;

use App\Entity\Rutina;
use App\Repository\RutinaRepository;
use App\Repository\EntrenadorRepository;
use App\Repository\UsuarioRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/rutinas')]
class RutinaController extends ApiController
{
    private $rutinaRepository;
    private $entrenadorRepository;
    private $usuarioRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        RutinaRepository $rutinaRepository,
        EntrenadorRepository $entrenadorRepository,
        UsuarioRepository $usuarioRepository
    ) {
        parent::__construct($entityManager, $serializer, $validator);
        $this->rutinaRepository = $rutinaRepository;
        $this->entrenadorRepository = $entrenadorRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    // Obtener id de la rutina
    #[Route('', name: 'app_rutinas_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $rutinas = $this->rutinaRepository->findAll();
        $data = $this->serializer->serialize($rutinas, 'json', ['groups' => 'rutina:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Obtener todas las rutinas
    #[Route('/{id}', name: 'app_rutinas_show', methods: ['GET'])]
    public function show(Rutina $rutina): JsonResponse
    {
        $data = $this->serializer->serialize($rutina, 'json', ['groups' => 'rutina:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Crear una nueva rutina
    #[Route('', name: 'app_rutinas_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $rutina = new Rutina();
        $rutina->setNombre($data['nombre'] ?? null);
        $rutina->setTipoRutina($data['tipo_rutina'] ?? null);
        $rutina->setSeries($data['series'] ?? null);
        $rutina->setCategoria($data['categoria'] ?? null);

        if (isset($data['descripcion'])) {
            $rutina->setDescripcion($data['descripcion']);
        }   

        // Asignar entrenador
        if (isset($data['entrenador_id'])) {
            $entrenador = $this->entrenadorRepository->find($data['entrenador_id']);
            if (!$entrenador) {
                return new JsonResponse(['error' => 'Entrenador no encontrado'], Response::HTTP_BAD_REQUEST);
            }
            $rutina->setEntrenador($entrenador);
        }
        // Asignar usuario
        if (isset($data['usuario_id'])) {
            $usuario = $this->usuarioRepository->find($data['usuario_id']);
            if (!$usuario) {
                return new JsonResponse(['error' => 'Usuario no encontrado'], Response::HTTP_BAD_REQUEST);
            }
            $rutina->setUsuario($usuario);
        }

        // Validar la rutina
        $errors= $this->validator->validate($rutina);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }

        // Guardar la rutina
        $this->entityManager->persist($rutina);
        $this->entityManager->flush();

        $data = $this->serializer->serialize($rutina, 'json', ['groups' => 'rutina:read']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    // Actualizar una rutina
    #[Route('/{id}', name: 'app_rutinas_update', methods: ['PUT'])]
    public function update(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['nombre'])) {
            $rutina->setNombre($data['nombre']);
        }   

        if (isset($data['tipo_rutina'])) {
            $rutina->setTipoRutina($data['tipo_rutina']);
        }   

        if (isset($data['series'])) {
            $rutina->setSeries($data['series']);
        }   

        if (isset($data['categoria'])) {
            $rutina->setCategoria($data['categoria']);
        }   

        if (isset($data['descripcion'])) {
            $rutina->setDescripcion($data['descripcion']);
        }   

        // Asignar entrenador
        if (isset($data['entrenador_id'])) {
            $entrenador = $this->entrenadorRepository->find($data['entrenador_id']);
            if (!$entrenador) {
                return new JsonResponse(['error' => 'Entrenador no encontrado'], Response::HTTP_BAD_REQUEST);
            }
            $rutina->setEntrenador($entrenador);
        }
        // Asignar usuario
        if (isset($data['usuario_id'])) {
            $usuario = $this->usuarioRepository->find($data['usuario_id']);
            if (!$usuario) {
                return new JsonResponse(['error' => 'Usuario no encontrado'], Response::HTTP_BAD_REQUEST);
            }
            $rutina->setUsuario($usuario);
        }

        // Validar la rutina
        $errors= $this->validator->validate($rutina);
        if (count($errors) > 0) {
            return $this->validationErrorResponse($errors);
        }

        // Guardar la rutina
        $this->entityManager->flush();

        $data = $this->serializer->serialize($rutina, 'json', ['groups' => 'rutina:read']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    // Eliminar una rutina
    #[Route('/{id}', name: 'app_rutinas_delete', methods: ['DELETE'])]
    public function delete(Rutina $rutina): JsonResponse
    {
        $this->entityManager->remove($rutina);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // Buscar rutinas por tipo
    #[Route('/buscar/tipo/{tipoRutina}', name: 'app_rutinas_buscar_tipo', methods: ['GET'])]
    public function buscarPorTipo(string $tipoRutina): JsonResponse
    {
        $rutinas = $this->rutinaRepository->findByTipoRutina($tipoRutina);
        $data = $this->serializer->serialize($rutinas, 'json', ['groups' => 'rutina:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Buscar rutinas por categoria
    #[Route('/buscar/categoria/{categoria}', name: 'app_rutinas_buscar_categoria', methods: ['GET'])]
    public function buscarPorCategoria(string $categoria): JsonResponse
    {
        $rutinas = $this->rutinaRepository->findByCategoria($categoria);
        $data = $this->serializer->serialize($rutinas, 'json', ['groups' => 'rutina:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Buscar rutinas por entrenador
    #[Route('/buscar/entrenador/{entrenadorId}', name: 'app_rutinas_buscar_entrenador', methods: ['GET'])]
    public function buscarPorEntrenador(int $entrenadorId): JsonResponse
    {
        $rutinas = $this->rutinaRepository->findByEntrenador($entrenadorId);
        $data = $this->serializer->serialize($rutinas, 'json', ['groups' => 'rutina:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Buscar rutinas por usuario
    #[Route('/buscar/usuario/{usuarioId}', name: 'app_rutinas_buscar_usuario', methods: ['GET'])]
    public function buscarPorUsuario(int $usuarioId): JsonResponse
    {
        $rutinas = $this->rutinaRepository->findByUsuario($usuarioId);
        $data = $this->serializer->serialize($rutinas, 'json', ['groups' => 'rutina:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}