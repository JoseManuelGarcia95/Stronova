<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UsuarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    // Buscar usuario por apellidos

    public function findByApellidos (string $apellidos)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.apellidos LIKE :apellidos')
            ->setParameter('apellidos', '%' . $apellidos . '%')
            ->orderBy('u.apellidos', 'ASC')
            ->addOrderBy('u.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Buscar usuario por objetivo 

    public function findByObjetivo (string $objetivo)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.objetivo LIKE :objetivo')
            ->setParameter('objetivo', '%' . $objetivo . '%')
            ->orderBy('u.apellidos', 'ASC')
            ->getQuery()
            ->getResult();
    }
}