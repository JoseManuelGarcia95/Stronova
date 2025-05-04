<?php

namespace App\Repository;

use App\Entity\RutinaEjercicio;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RutinaEjercicioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RutinaEjercicio::class);
    }


    // Buscar por nombre de ejercicio

    public function findByNombreEjercicio(string $nombreEjercicio)
    {
        return $this->createQueryBuilder('re')
            ->join('re.ejercicio', 'e')
            ->andWhere('e.nombre LIKE :nombre')
            ->setParameter('nombre', '%' . $nombreEjercicio . '%')
            ->orderBy('re.orden', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Buscar por nombre de rutina

    public function findByNombreRutina(string $nombreRutina)
    {
        return $this->createQueryBuilder('re')
            ->join('re.rutina', 'r')
            ->andWhere('r.nombre LIKE :nombre')
            ->setParameter('nombre', '%' . $nombreRutina . '%')
            ->orderBy('re.nombre', 'ASC')
            ->addOrderBy('re.orden', 'ASC')
            ->getQuery()
            ->getResult();
    }
}