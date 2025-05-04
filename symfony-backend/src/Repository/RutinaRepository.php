<?php

namespace App\Repository;

use App\Entity\Rutina;
use App\Entity\Entrenador;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RutinaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rutina::class);
    }

    // Buscar por tipo de rutina

    public function findByTipoRutina(string $tipoRutina)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.tipo_rutina = :tipoRutina')
            ->setParameter('tipoRutina', $tipoRutina)
            ->orderBy('r.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Buscar rutinas por categoria 

    public function findByCategoria(string $categoria)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.categoria = :categoria')
            ->setParameter('categoria', $categoria)
            ->orderBy('r.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Buscar rutinas por entrenador

    public function findByEntrenador(string $nombreEntrenador)
    {
        return $this->createQueryBuilder('r')
            ->join('r.entrenador', 'e')
            ->andWhere('e.nombre LIKE :nombre OR e.apellidos LIKE :nombre')
            ->setParameter('nombre', '%' . $nombreEntrenador . '%')
            ->orderBy('r.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Buscar rutinas por usuario
    public function findByUsuario(string $nombreUsuario)
    {
        return $this->createQueryBuilder('r')
            ->join('r.usuario', 'u')
            ->andWhere('u.nombre LIKE :nombre OR u.apellidos LIKE :nombre')
            ->setParameter('nombre', '%' . $nombreUsuario . '%')
            ->orderBy('r.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}