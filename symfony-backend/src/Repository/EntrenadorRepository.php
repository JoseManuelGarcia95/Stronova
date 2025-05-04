<?php

namespace App\Repository;

use App\Entity\Entrenador;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EntrenadorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entrenador::class);
    }

    // Buscar por apellidos

    public function findByApellidos(string $apellidos)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.apellidos LIKE :apellidos')
            ->setParameter('apellidos', '%' . $apellidos . '%')
            ->orderBy('e.apellidos', 'ASC')
            ->addOrderBy('e.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Buscar por especialidad

    public function findByEspecialidad(string $especialidad)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.especialidad LIKE :especialidad')
            ->setParameter('especialidad', '%' . $especialidad . '%')
            ->orderBy('e.apellidos', 'ASC')
            ->getQuery()
            ->getResult();
    }
}