<?php

namespace App\Controller;

use App\Entity\ResultadoEntreno;
use App\Repository\ResultadoEntrenoRepository;
use App\Repository\RutinaRepository;
use App\Repository\UsuarioRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/resultado-entrenos')]
class ResultadoEntrenoController extends ApiController
{
    private $resultadoEntrenoRepository;
    private $rutinaRepository;
    private $usuarioRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        ResultadoEntrenoRepository $resultadoEntrenoRepository,
        RutinaRepository $rutinaRepository,
        UsuarioRepository $usuarioRepository
    ) {
        parent::__construct($entityManager, $serializer, $validator);
        $this->resultadoEntrenoRepository = $resultadoEntrenoRepository;
        $this->rutinaRepository = $rutinaRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    // Obtener id de los resultados de entreno
    #[Route('', name: 'app_resultado_entrenos_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $resultadoEntrenos = $this->resultadoEntrenoRepository->findAll();
        $data = $this->serializer->serialize($resultadoEntrenos, 'json', ['groups' => 'resultado_entreno:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Obtener todos los resultados de entreno
    #[Route('/{id}', name: 'app_resultado_entrenos_show', methods: ['GET'])]
    public function show(ResultadoEntreno $resultadoEntreno): JsonResponse
    {
        $data = $this->serializer->serialize($resultadoEntreno, 'json', ['groups' => 'resultado_entreno:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    // Crear un resultado de entreno
    #[Route('', name: 'app_resultado_entrenos_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $resultadoEntreno = new ResultadoEntreno();

        // Establecer las relaciones
        if (isset($data['usuario_id'])) {
            $usuario = $this->usuarioRepository->find($data['usuario_id']);
            if (!$usuario) {
                return new JsonResponse(['error' => 'Usuario no encontrado'], Response::HTTP_BAD_REQUEST);
            }
            $resultadoEntreno->setUsuario($usuario);
        }

        if (isset($data['rutina_id'])) {
            $rutina = $this->rutinaRepository->find($data['rutina_id']);
            if (!$rutina) {
                return new JsonResponse(['error' => 'Rutina no encontrada'], Response::HTTP_BAD_REQUEST);
            }
            $resultadoEntreno->setRutina($rutina);
        }

        // Establecer fecha
        if (isset($data['fecha'])) {
            $fecha = new \DateTime($data['fecha']);
            $resultadoEntreno->setFecha($fecha);
        } else {
            $resultadoEntreno->setFecha(new \DateTime());
        }

        // Establecer otros atributos

        if (isset($data['duracion_minutos'])) {
            $resultadoEntreno->setDuracionMinutos($data['duracion_minutos']);
        }

        if (isset($data['dificultad_percibida'])) {
            $resultadoEntreno->setDificultadPercibida($data['dificultad_percibida']);
        }

        if (isset($data['comentarios'])) {
            $resultadoEntreno->setComentarios($data['comentarios']);
        }

        if (isset($data['completado'])) {
            $resultadoEntreno->setCompletado($data['completado']);
        } else {
            $resultadoEntreno->setCompletado(true);
        }

         // Validar datos
         $errors = $this->validator->validate($resultadoEntreno);
         if (count($errors) > 0) {
             return $this->validationErrorResponse($errors);
         }

        // Guardar resultado de entreno
        $this->entityManager->persist($resultadoEntreno);
        $this->entityManager->flush();

        $data = $this->serializer->serialize($resultadoEntreno, 'json', ['groups' => 'resultado_entreno:read']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    // Actualizar un resultado de entreno
    #[Route('/{id}', name: 'app_resultado_entrenos_update', methods: ['PUT'])]
    public function update(Request $request, ResultadoEntreno $resultadoEntreno): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Actualizar las relaciones
        if (isset($data['usuario_id'])) {
            $usuario = $this->usuarioRepository->find($data['usuario_id']);
            if (!$usuario) {
                return new JsonResponse(['error' => 'Usuario no encontrado'], Response::HTTP_BAD_REQUEST);
            }
            $resultadoEntreno->setUsuario($usuario);
        }

        if (isset($data['rutina_id'])) {
            $rutina = $this->rutinaRepository->find($data['rutina_id']);
            if (!$rutina) {
                return new JsonResponse(['error' => 'Rutina no encontrada'], Response::HTTP_BAD_REQUEST);
            }
            $resultadoEntreno->setRutina($rutina);
        }

        // Actualizar fecha
        if (isset($data['fecha'])) {
            $fecha = new \DateTime($data['fecha']);
            $resultadoEntreno->setFecha($fecha);
        }

        // Actualizar otros atributos
        if (isset($data['duracion_minutos'])) {
            $resultadoEntreno->setDuracionMinutos($data['duracion_minutos']);
        }

        if (isset($data['dificultad_percibida'])) {
            $resultadoEntreno->setDificultadPercibida($data['dificultad_percibida']);
        }

        if (isset($data['comentarios'])) {
            $resultadoEntreno->setComentarios($data['comentarios']);
        }

        if (isset($data['completado'])) {
            $resultadoEntreno->setCompletado($data['completado']);
        } else {
            $resultadoEntreno->setCompletado(true);
        }

         // Validar datos
         $errors = $this->validator->validate($resultadoEntreno);
         if (count($errors) > 0) {
             return $this->validationErrorResponse($errors);
         }

        // Guardar resultado de entreno
        $this->entityManager->flush();

        $data = $this->serializer->serialize($resultadoEntreno, 'json', ['groups' => 'resultado_entreno:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
        }

    // Eliminar un resultado de entreno
    #[Route('/{id}', name: 'app_resultado_entrenos_delete', methods: ['DELETE'])]
    public function delete(ResultadoEntreno $resultadoEntreno): JsonResponse
    {
        $this->entityManager->remove($resultadoEntreno);
        $this->entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // Buscar resultado de entreno por nombre usuario
    #[Route('/usuario/nombre/{nombreUsuario}', name: 'app_resultado_entrenos_buscar_por_nombre', methods: ['GET'])]
    public function resultadosPorNombreUsuario(string $nombreUsuario): JsonResponse
    {
        $resultados = $this->resultadoEntrenoRepository->findByNombreUsuario($nombreUsuario);
        $data = $this->serializer->serialize($resultados, 'json', ['groups' => 'resultado_entreno:read']);
        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }
}
