<?php

namespace App\Repository;

use App\Entity\Ejercicio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EjercicioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ejercicio::class);
    }

    // Buscar ejercicio por nombre

    public function findByNombre(string $nombre)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.nombre LIKE :nombre')
            ->setParameter('nombre', '%' . $nombre . '%')
            ->orderBy('e.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Buscar ejercicio por categoría

    public function findByCategoria(string $categoria)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.categoria = :categoria')
            ->setParameter('categoria', $categoria)
            ->orderBy('e.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Buscar ejercicio por dificultad

    public function findByDificultad(string $dificultad)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.dificultad = :dificultad')
            ->setParameter('dificultad', $dificultad)
            ->orderBy('e.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}