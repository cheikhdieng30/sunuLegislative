<?php

namespace App\Repository;

use App\Entity\Resultat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Resultat>
 *
 * @method Resultat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Resultat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Resultat[]    findAll()
 * @method Resultat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResultatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resultat::class);
    }

    public function add(Resultat $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Resultat $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Resultat[] Returns an array of Resultat objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    // public function findOneBySomeField(): array
    // {
    //     return $this->createQueryBuilder('r')
    //         ->select('SUM(r.wallu) as \'wallu senegal\'')
    //         ->getQuery()
    //         ->getResult();
    // }

    public function findByDepartement()
    {
        return $this->getEntityManager()
            ->createQuery(
                ' SELECT B.nomCir,  
                SUM(R.nbVotant) as nbVotant,
                SUM(R.bulletinNull) as bulletinNull,
                SUM(R.bulletinExp) as bulletinExp
                    FROM App\Entity\User U,
                    App\Entity\Resultat R,
                    App\Entity\BureauVote B
                    WHERE  B.id = U.BV
                    AND R.user = U.id  
                    GROUP BY B.nomCir  '
            )->getResult();
    }

    public function findNombreTotalVoix()
    {
        return $this->getEntityManager()
            ->createQuery(
                " SELECT C.nom, SUM(CR.nombre) as nbVoix
                FROM App\Entity\Resultat R, App\Entity\Coalition C, App\Entity\ResultatCoalition CR
                WHERE C.id = CR.coaltion AND R.id = CR.resulat
                GROUP BY C.nom
                 "
            )->getResult();
    }

    public function findNombreTotalVoixCoalition()
    {
        return $this->getEntityManager()
            ->createQuery(
                " SELECT 
                R
                FROM  App\Entity\Resultat R "
            )->getResult();
    }

    public function findNombreBureauVoteCoalition()
    {
        return $this->getEntityManager()
            ->createQuery(
                " SELECT 
                    B.nomCir , COUNT (B.nomBV) as nbBureauVote
                    FROM  App\Entity\BureauVote B  GROUP BY B.nomCir
                 "
            )->getResult();
    }

    public function findNombreResultBureauVoteCoalition()
    {
        return $this->getEntityManager()
            ->createQuery(
                " SELECT 
                    B.nomCir , COUNT (R.bulletinExp) as NBresultat
                    FROM  App\Entity\BureauVote B,  App\Entity\Resultat R,  App\Entity\User U
                    WHERE R.user = U.id AND B.id = U.BV
                    GROUP BY B.nomCir
                 "
            )->getResult();
    }

    // SELECT BV.nom_cir, COUNT(BV.nom_bv), COUNT(R.bulletin_exp) FROM resultat R, bureau_vote BV, user U WHERE R.user_id = U.id AND BV.id = U.bv_id GROUP BY BV.nom_cir

    // SELECT  r.user_id , bureau_vote.nom_cir , retenus.nom , SUM(r.nb_inscrit), SUM(r.nb_votant), SUM(r.bulletin_null), SUM(r.bulletin_exp)  FROM resultat as r, bureau_vote, user, retenus WHERE r.retenus_id = retenus.id AND bureau_vote.id = user.bv_id and r.user_id = user.id   GROUP BY r.user_id, bureau_vote.nom_cir, retenus.nom

    // SELECT   bureau_vote.nom_cir ,  SUM(r.nb_inscrit), SUM(r.nb_votant), SUM(r.bulletin_null), SUM(r.bulletin_exp)  FROM resultat as r, bureau_vote, user, retenus WHERE r.retenus_id = retenus.id AND bureau_vote.id = user.bv_id and r.user_id = user.id  GROUP BY  bureau_vote.nom_cir

    // SELECT retenus.nom, SUM(resultat.bulletin_exp) as nbVoix FROM resultat, retenus WHERE retenus.id = resultat.retenus_id GROUP BY retenus.nom

    // SELECT b.nom_cir , COUNT(b.nom_bv) FROM  bureau_vote as b GROUP BY b.nom_cir
}
