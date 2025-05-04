<?php

namespace App\Repository;

use App\Entity\ResultadoEntreno;
use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ResultadoEntrenoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResultadoEntreno::class);
    }

    // Buscar resultado de entreno por nombre usuario

    public function findByNombreUsuario(string $nombreUsuario)
    {
        return $this->createQueryBuilder('re')
            ->join('re.usuario', 'u')
            ->andWhere('u.nombre LIKE :nombre OR u.apellidos LIKE :nombre')
            ->setParameter('nombre', '%' . $nombreUsuario . '%')
            ->orderBy('re.fecha', 'DESC')
            ->getQuery()
            ->getResult();
    }
}