<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\RetenusRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=RetenusRepository::class)
 * @UniqueEntity(
 *     fields={"nom"},
 *     message="Cette coaliation existe dèja !"
 * )
 */
class Retenus
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Veuillez remplir ce champ")
     */
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity=Resultat::class, mappedBy="retenus")
     */
    private $resultats;

    public function __construct()
    {
        $this->resultats = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function __toString()
    {
        return $this->nom;
    }

    /**
     * @return Collection<int, Resultat>
     */
    public function getResultats(): Collection
    {
        return $this->resultats;
    }

    public function addResultat(Resultat $resultat): self
    {
        if (!$this->resultats->contains($resultat)) {
            $this->resultats[] = $resultat;
            $resultat->setRetenus($this);
        }

        return $this;
    }

    public function removeResultat(Resultat $resultat): self
    {
        if ($this->resultats->removeElement($resultat)) {
            // set the owning side to null (unless already changed)
            if ($resultat->getRetenus() === $this) {
                $resultat->setRetenus(null);
            }
        }

        return $this;
    }
}
