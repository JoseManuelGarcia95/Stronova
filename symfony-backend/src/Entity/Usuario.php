<?php 
namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)] 
class Usuario 
{ // Atributos de la Entidad Usuario
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column] 
    private ?int $id = null;

    #[ORM\Column(length: 255)] 
    private ?string $nombre = null;

    #[ORM\Column(length: 255)] 
    private ?string $apellidos = null;

    #[ORM\Column(length: 255)] 
    private ?string $email = null;

    #[ORM\Column(length: 255)] 
    private ?string $genero = null;

    // Precision:5 es el número total de dígitos y Scale:2 es el número de decimales
    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true)] 
    private ?float $altura = null;

    #[ORM\Column(type: 'decimal', precision: 6, scale: 2, nullable: true)] 
    private ?float $peso_inicial = null;

    #[ORM\Column(length: 255, nullable: true)] 
    private ?string $lesiones = null;

    #[ORM\Column(length: 255, nullable: true)] 
    private ?string $objetivo = null;

    // Relaciones con otras entidades

    #[ORM\ManyToOne(inversedBy: 'usuarios')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Entrenador $entrenador = null;

    #[ORM\OneToMany(mappedBy: 'usuario', targetEntity: Rutina::class, orphanRemoval: true)]
    private Collection $rutinas;

    #[ORM\OneToMany(mappedBy: 'usuario', targetEntity: ResultadoEntreno::class, orphanRemoval: true)]
    private Collection $resultadosEntrenos;

    public function __construct()
    {
        $this->rutinas = new ArrayCollection();
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

    public function getGenero(): ?string
    {
        return $this->genero;
    }
    public function setGenero(string $genero): static
    {
        $this->genero = $genero;

        return $this;
    }

    public function getAltura(): ?float
    {
        return $this->altura;
    }
    public function setAltura(float $altura): static
    {
        $this->altura = $altura;

        return $this;
    }

    public function getPesoInicial(): ?float
    {
        return $this->peso_inicial;
    }
    public function setPesoInicial(float $peso_inicial): static
    {
        $this->peso_inicial = $peso_inicial;

        return $this;
    }

    public function getLesiones(): ?string
    {
        return $this->lesiones;
    }
    public function setLesiones(string $lesiones): static
    {
        $this->lesiones = $lesiones;

        return $this;
    }

    public function getObjetivo(): ?string
    {
        return $this->objetivo;
    }
    public function setObjetivo(string $objetivo): static
    {
        $this->objetivo = $objetivo;

        return $this;
    }

    public function getEntrenador(): ?Entrenador
    {
        return $this->entrenador;
    }
    public function setEntrenador(Entrenador $entrenador): static
    {
        $this->entrenador = $entrenador;

        return $this;
    }

    // Obtener todas las rutinas de un usuario
    public function getRutinas(): Collection
    {
        return $this->rutinas;
    }

    // Asignar una rutina a un usuario
    public function addRutina(Rutina $rutina): static
    {
        if (!$this->rutinas->contains($rutina)) {
            $this->rutinas->add($rutina);
            $rutina->setUsuario($this);
        }

        return $this;
    }   
    // Eliminar una rutina de un usuario
    public function removeRutina(Rutina $rutina): static
    {
        if ($this->rutinas->removeElement($rutina)) {
            if ($rutina->getUsuario() === $this) {
                $rutina->setUsuario(null);
            }
        }

        return $this;
    }

    // Obtener todos los resultados de entrenos de un usuario
    public function getResultadosEntrenos(): Collection
    {
        return $this->resultadosEntrenos;
    }

    // Asignar un resultado de entreno a un usuario
    public function addResultadosEntreno(ResultadoEntreno $resultadoEntreno): static
    {
        if (!$this->resultadosEntrenos->contains($resultadoEntreno)) {
            $this->resultadosEntrenos->add($resultadoEntreno);
            $resultadoEntreno->setUsuario($this);
        }

        return $this;
    }

    // Eliminar un resultado de entreno de un usuario
    public function removeResultadosEntreno(ResultadoEntreno $resultadoEntreno): static
    {
        if ($this->resultadosEntrenos->removeElement($resultadoEntreno)) {
            if ($resultadoEntreno->getUsuario() === $this) {
                $resultadoEntreno->setUsuario(null);
            }
        }

        return $this;
    }
}