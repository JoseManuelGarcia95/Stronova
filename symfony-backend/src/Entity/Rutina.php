<?php

namespace App\Entity;

use App\Repository\RutinaRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RutinaRepository::class)]
class Rutina
{
    // Atributos de la Entidad Rutina
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['rutina:read', 'usuario:read', 'entrenador:read', 'resultado_entreno:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['rutina:read', 'rutina:write', 'usuario:read', 'entrenador:read', 'resultado_entreno:read'])]
    private ?string $nombre = null;

    #[ORM\Column(length: 100)]
    #[Groups(['rutina:read', 'rutina:write', 'usuario:read', 'entrenador:read'])]
    private ?string $tipo_rutina = null;

    #[ORM\Column]
    #[Groups(['rutina:read', 'rutina:write'])]
    private ?int $series = null;

    #[ORM\Column(length: 100)]
    #[Groups(['rutina:read', 'rutina:write', 'usuario:read', 'entrenador:read'])]
    private ?string $categoria = null;

    #[ORM\Column(length: 1000, nullable: true)]
    #[Groups(['rutina:read', 'rutina:write'])]
    private ?string $descripcion = null;

    // Relaciones con otras entidades
    #[ORM\ManyToOne(inversedBy: 'rutinasCreadas')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['rutina:read'])]
    private ?Entrenador $entrenador = null;

    #[ORM\ManyToOne(inversedBy: 'rutinas')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['rutina:read'])]
    private ?Usuario $usuario = null;

    #[ORM\OneToMany(mappedBy: 'rutina', targetEntity: RutinaEjercicio::class, orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['rutina:read'])]
    private Collection $rutinaEjercicios;

    #[ORM\OneToMany(mappedBy:'rutina', targetEntity: ResultadoEntreno::class)]
    #[Groups(['rutina:read'])]
    private Collection $resultadosEntrenos;

    public function __construct()
    {
        $this->rutinaEjercicios = new ArrayCollection();
        $this->resultadosEntrenos = new ArrayCollection();
    }

    // Getters y Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }
    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getTipoRutina(): ?string
    {
        return $this->tipo_rutina;
    }
    public function setTipoRutina(string $tipo_rutina): static
    {
        $this->tipo_rutina = $tipo_rutina;

        return $this;
    }

    public function getSeries(): ?int
    {
        return $this->series;
    }
    public function setSeries(int $series): static
    {
        $this->series = $series;

        return $this;
    }

    public function getCategoria(): ?string
    {
        return $this->categoria;
    }
    public function setCategoria(string $categoria): static
    {
        $this->categoria = $categoria;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }
    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getEntrenador(): ?Entrenador
    {
        return $this->entrenador;
    }
    public function setEntrenador(?Entrenador $entrenador): static
    {
        $this->entrenador = $entrenador;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }
    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    // Obtener todos los ejercicios de una rutina
    public function getRutinaEjercicios(): Collection
    {
        return $this->rutinaEjercicios;
    }
    // Añadir un ejercicio a una rutina
    public function addRutinaEjercicio(RutinaEjercicio $rutinaEjercicio): static
    {
        if (!$this->rutinaEjercicios->contains($rutinaEjercicio)) {
            $this->rutinaEjercicios->add($rutinaEjercicio);
            $rutinaEjercicio->setRutina($this);
        }

        return $this;
    }

    // Eliminar un ejercicio de una rutina
    public function removeRutinaEjercicio(RutinaEjercicio $rutinaEjercicio): static
    {
        if ($this->rutinaEjercicios->removeElement($rutinaEjercicio)) {
            if ($rutinaEjercicio->getRutina() === $this) {
                $rutinaEjercicio->setRutina(null);
            }
        }

        return $this;
    }

    // Obtener todos los resultados de entrenos de una rutina
    public function getResultadosEntrenos(): Collection
    {
        return $this->resultadosEntrenos;
    }

    // Añadir un resultado de entreno a una rutina
    public function addResultadosEntreno(ResultadoEntreno $resultadoEntreno): static
    {
        if (!$this->resultadosEntrenos->contains($resultadoEntreno)) {
            $this->resultadosEntrenos->add($resultadoEntreno);
            $resultadoEntreno->setRutina($this);
        }

        return $this;
    }

    // Eliminar un resultado de entreno de una rutina
    public function removeResultadosEntreno(ResultadoEntreno $resultadoEntreno): static
    {
        if ($this->resultadosEntrenos->removeElement($resultadoEntreno)) {
            if ($resultadoEntreno->getRutina() === $this) {
                $resultadoEntreno->setRutina(null);
            }
        }

        return $this;
    }

    // Obtener ID del usuario asignado
    #[Groups(['rutina:read', 'usuario:read', 'entrenador:read'])]
    public function getUsuarioId(): ?int
    {
        return $this->usuario ? $this->usuario->getId() : null;
    }

    // Obtener ID del entrenador
    #[Groups(['rutina:read', 'usuario:read', 'entrenador:read'])]
    public function getEntrenadorId(): ?int
    {
        return $this->entrenador ? $this->entrenador->getId() : null;
    }
}