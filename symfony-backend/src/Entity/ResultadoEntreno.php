<?php

namespace App\Entity;

use App\Repository\ResultadoEntrenoRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: ResultadoEntrenoRepository::class)]
class ResultadoEntreno
{
    // Atributos de la Entidad ResultadoEntreno
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\Column(nullable: true)]
    private ?int $duracion_minutos = null;

    #[ORM\Column(nullable: true)]
    private ?int $dificultad_percibida = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $comentarios = null;

    #[ORM\Column(nullable: true)]
    private ?bool $completado = null;

    // Relaciones con otras entidades
    #[ORM\ManyToOne(inversedBy: 'resultadosEntrenos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\ManyToOne(inversedBy: 'resultadosEntrenos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rutina $rutina = null;

    // Getters y Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;

        return $this;
    }

    public function getDuracionMinutos(): ?int
    {
        return $this->duracion_minutos;
    }
    public function setDuracionMinutos(?int $duracion_minutos): static
    {
        $this->duracion_minutos = $duracion_minutos;

        return $this;
    }

    public function getDificultadPercibida(): ?int
    {
        return $this->dificultad_percibida;
    }
    public function setDificultadPercibida(?int $dificultad_percibida): static
    {
        $this->dificultad_percibida = $dificultad_percibida;

        return $this;
    }

    public function getComentarios(): ?string
    {
        return $this->comentarios;
    }
    public function setComentarios(?string $comentarios): static
    {
        $this->comentarios = $comentarios;

        return $this;
    }

    public function isCompletado(): ?bool
    {
        return $this->completado;
    }
    public function setCompletado(?bool $completado): static
    {
        $this->completado = $completado;

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

    public function getRutina(): ?Rutina
    {
        return $this->rutina;
    }
    public function setRutina(?Rutina $rutina): static
    {
        $this->rutina = $rutina;

        return $this;
    }

}