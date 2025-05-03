<?php

namespace App\Entity;

use App\Repository\EntrenadoresRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: EntrenadorRepository::class)]
class Entrenador
{ // Atributos de la Entidad Entrenador
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $apellidos = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $especialidad = null;

    #[ORM\Column]
    private ?int $clientes_activos = null;

    // Relaciones con otras entidades
    #[ORM\OneToMany(mappedBy:'entrenador', targetEntity: Usuario::class)]
    private Collection $usuarios;

    #[ORM\OneToMany(mappedBy: 'entrenador', targetEntity: Rutina::class)]
    private Collection $rutinasCreadas;

    public function __construct()
    {
        $this->usuarios = new ArrayCollection();
        $this->rutinasCreadas = new ArrayCollection();
        $this->clientes_activos = 0;
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

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }
    public function setApellidos(string $apellidos): static
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getEspecialidad(): ?string
    {
        return $this->especialidad;
    }   
    public function setEspecialidad(?string $especialidad): static
    {
        $this->especialidad = $especialidad;

        return $this;
    }

    public function getClientesActivos(): ?int
    {
        return $this->clientes_activos;
    }
    public function setClientesActivos(int $clientes_activos): static
    {
        $this->clientes_activos = $clientes_activos;

        return $this;
    }

    // Obtener todos los usuarios de un entrenador
    public function getUsuarios(): Collection
    {
        return $this->usuarios;
    }

    // Añadir un usuario a un entrenador
    public function addUsuario(Usuario $usuario): static
    {
        if (!$this->usuarios->contains($usuario)) {
            $this->usuarios->add($usuario);
            $usuario->setEntrenadores($this);
            $this->actualizarContadorClientes();
        }

        return $this;
    }

    // Eliminar un usuario de un entrenador
    public function removeUsuario(Usuario $usuario): static
    {
        if ($this->usuarios->removeElement($usuario)) {
            if ($usuario->getEntrenadores() === $this) {
                $usuario->setEntrenadores(null);
            }
            $this->actualizarContadorClientes();
        }

        return $this;
    }

    // Obtener todas las rutinas creadas por un entrenador
    public function getRutinasCreadas(): Collection
    {
        return $this->rutinasCreadas;
    }
    // Añadir una rutina creada por un entrenador
    public function addRutinasCreada(Rutina $rutina): static
    {
        if (!$this->rutinasCreadas->contains($rutina)) {
            $this->rutinasCreadas->add($rutina);
            $rutina->setEntrenadores($this);
        }

        return $this;
    }

    // Eliminar una rutina creada por un entrenador
    public function removeRutinasCreada(Rutina $rutina): static
    {
        if ($this->rutinasCreadas->removeElement($rutina)) {
            if ($rutina->getEntrenadores() === $this) {
                $rutina->setEntrenadores(null);
            }
        }

        return $this;
    }

    // Actualizar el contador de clientes activos
    public function actualizarContadorClientes(): void
    {
        $this->clientes_activos = $this->usuarios->count();
    }
}