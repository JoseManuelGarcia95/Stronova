<?php

namespace App\Entity;

use App\Repository\EjercicioRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EjercicioRepository::class)]
class Ejercicio
{
    // Atributos de la Entidad Ejercicio
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ejercicio:read', 'rutina_ejercicio:read', 'rutina:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['ejercicio:read', 'ejercicio:write', 'rutina_ejercicio:read', 'rutina:read'])]
    private ?string $nombre = null;

    #[ORM\Column(length: 1000)]
    #[Groups(['ejercicio:read', 'ejercicio:write', 'rutina_ejercicio:read'])]
    private ?string $descripcion = null;

    #[ORM\Column(length: 50)]
    #[Groups(['ejercicio:read', 'ejercicio:write', 'rutina_ejercicio:read'])]
    private ?string $dificultad = null;

    #[ORM\Column(length: 100)]
    #[Groups(['ejercicio:read', 'ejercicio:write', 'rutina_ejercicio:read'])]
    private ?string $categoria = null;

    // Relaciones con otras entidades
    #[ORM\OneToMany(mappedBy: 'ejercicio', targetEntity: RutinaEjercicio::class, orphanRemoval: true)]
    #[Groups(['ejercicio:read'])]
    private Collection $rutinaEjercicios;

    public function __construct()
    {
        $this->rutinaEjercicios = new ArrayCollection();
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

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }
    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getDificultad(): ?string
    {
        return $this->dificultad;
    }
    public function setDificultad(string $dificultad): static
    {
        $this->dificultad = $dificultad;

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

    public function getRutinaEjercicios(): Collection
    {
        return $this->rutinaEjercicios;
    }
    public function addRutinaEjercicio(RutinaEjercicio $rutinaEjercicio): static
    {
        if (!$this->rutinaEjercicios->contains($rutinaEjercicio)) {
            $this->rutinaEjercicios->add($rutinaEjercicio);
            $rutinaEjercicio->setEjercicio($this);
        }

        return $this;
    }

    public function removeRutinaEjercicio(RutinaEjercicio $rutinaEjercicio): static
    {
        if ($this->rutinaEjercicios->removeElement($rutinaEjercicio)) {
            if ($rutinaEjercicio->getEjercicio() === $this) {
                $rutinaEjercicio->setEjercicio(null);
            }
        }

        return $this;
    }
}