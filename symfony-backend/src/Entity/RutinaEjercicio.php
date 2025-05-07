<?php

namespace App\Entity;

use App\Repository\RutinaEjercicioRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RutinaEjercicioRepository::class)]
class RutinaEjercicio
{
    // Atributos de la Entidad RutinaEjercicio
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['rutina_ejercicio:read', 'rutina:read', 'ejercicio:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column]
    #[Groups(['rutina_ejercicio:read', 'rutina_ejercicio:write', 'rutina:read', 'ejercicio:read'])]
    private ?int $series = null;

    #[ORM\Column]
    #[Groups(['rutina_ejercicio:read', 'rutina_ejercicio:write', 'rutina:read', 'ejercicio:read'])]
    private ?int $repeticiones = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['rutina_ejercicio:read', 'rutina_ejercicio:write', 'rutina:read', 'ejercicio:read'])]
    private ?int $descanso_segundos = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['rutina_ejercicio:read', 'rutina_ejercicio:write', 'rutina:read'])]
    private ?int $orden = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['rutina_ejercicio:read', 'rutina_ejercicio:write', 'rutina:read'])]
    private ?string $notas = null;

    // Relaciones con otras entidades
    #[ORM\ManyToOne(inversedBy: 'rutinaEjercicios')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['rutina_ejercicio:read', 'ejercicio:read'])]
    private ?Rutina $rutina = null;

    #[ORM\ManyToOne(inversedBy: 'rutinaEjercicios')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['rutina_ejercicio:read', 'rutina:read'])]
    private ?Ejercicio $ejercicio = null;

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

    public function getSeries(): ?int
    {
        return $this->series;
    }
    public function setSeries(int $series): static
    {
        $this->series = $series;

        return $this;
    }

    public function getRepeticiones(): ?int
    {
        return $this->repeticiones;
    }
    public function setRepeticiones(int $repeticiones): static
    {
        $this->repeticiones = $repeticiones;

        return $this;
    }

    public function getDescansoSegundos(): ?int
    {
        return $this->descanso_segundos;
    }
    public function setDescansoSegundos(?int $descanso_segundos): static
    {
        $this->descanso_segundos = $descanso_segundos;

        return $this;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }
    public function setOrden(?int $orden): static
    {
        $this->orden = $orden;

        return $this;
    }

    public function getNotas(): ?string
    {
        return $this->notas;
    }
    public function setNotas(?string $notas): static
    {
        $this->notas = $notas;

        return $this;
    }

    public function getRutina(): ?Rutina
    {
        return $this->rutina;
    }
    public function setRutina(?Rutina $rutina): static
    {
        $this->rutina = $rutina;

        return $this;
    }

    public function getEjercicio(): ?Ejercicio
    {
        return $this->ejercicio;
    }
    public function setEjercicio(?Ejercicio $ejercicio): static
    {
        $this->ejercicio = $ejercicio;

        return $this;
    }
}