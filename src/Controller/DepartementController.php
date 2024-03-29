<?php

namespace App\Controller;

use Exception;
use App\Entity\Upload;
use App\Form\UploadType;
use App\Entity\Departement;
use App\Form\DepartementType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DepartementRepository;
use function PHPUnit\Framework\returnSelf;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/departement")
 * @IsGranted("ROLE_ADMIN")
 */
class DepartementController extends AbstractController
{
    private $slugger;
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * @Route("/", name="app_departement_index", methods={"GET"})
     */
    public function index(DepartementRepository $departementRepository): Response
    {
        return $this->render('departement/index.html.twig', [
            'departements' => $departementRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="app_departement_new", methods={"GET", "POST"})
     */
    public function new(Request $request, DepartementRepository $departementRepository): Response
    {
        $departement = new Departement();
        $form = $this->createForm(DepartementType::class, $departement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $nom = $data->getNom();
            $commune = $data->getCommune();
            $departement->setSlug($this->slugger->slug($nom . '' . $commune));
            $departementRepository->add($departement, true);
            $this->addFlash('success', 'Cette circonscription a été bien ajoutée');
            return $this->redirectToRoute('app_departement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('departement/new.html.twig', [
            'departement' => $departement,
            'form' => $form,
        ]);
    }

    // /**
    //  * @Route("/{id}", name="app_departement_show", methods={"GET"})
    //  */
    // public function show(Departement $departement): Response
    // {
    //     return $this->render('departement/show.html.twig', [
    //         'departement' => $departement,
    //     ]);
    // }

    /**
     * @Route("/{id}/edit", name="app_departement_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Departement $departement, DepartementRepository $departementRepository): Response
    {
        $form = $this->createForm(DepartementType::class, $departement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $nom = $data->getNom();
            $commune = $data->getCommune();
            $departement->setSlug($this->slugger->slug($nom . '' . $commune));
            $departementRepository->add($departement, true);
            $this->addFlash('success', 'Cette circonscription a été bien modifiée');
            return $this->redirectToRoute('app_departement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('departement/edit.html.twig', [
            'departement' => $departement,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}/delete", name="app_departement_delete")
     */
    public function delete(Departement $departement): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($departement);
        $entityManager->flush();
        $this->addFlash('success', 'Cette circonscription a été bien supprimée');

        return $this->redirectToRoute('app_departement_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/add-csv", name="app_add_csv")
     */
    public function addBy(Request $request, EntityManagerInterface $entityManagerInterface, DepartementRepository $departementRepository): Response

    {

        $upload = new Upload();
        $form = $this->createForm(UploadType::class, $upload);
        $departements = $departementRepository->findAll();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $fileName = $request->files->get("upload");
            $fileNamePath = $fileName['file']->getRealPath();
            if ($fileName['file']->guessExtension() == "xlsx") {
                # code...
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
            }
            if ($fileName['file']->guessExtension() == "xls") {
                # code...
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls;
            }
            if ($fileName['file']->guessExtension() == "csv") {
                # code...
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv;
            }
            if ($fileName['file']->guessExtension() == "txt") {
                # code...
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv;
            }

            $spreadsheet = $reader->load($fileNamePath);
            $data = $spreadsheet->getActiveSheet()->toArray();
            $data = array_filter($data, function ($v) {
                return array_filter($v) != array();
            });
            $count = "0";
            foreach ($data as  $row) {
                // dd($data);
                if ($count > 0) {
                    // try {
                    $departement = new Departement();
                    $nom = $row['0'];
                    $commune  = $row['1'];
                    $slug = $this->slugger->slug($nom . '' . $commune);

                    foreach ($departements as $key => $value) {
                        if ($value->getNom() === $nom) {
                            $this->addFlash('error', 'certaines circonscriptions existent dèja !');
                            return $this->redirectToRoute('app_add_csv', [], Response::HTTP_SEE_OTHER);
                        }
                    }
                    $departement->setNom($nom)
                        ->setCommune($commune)
                        ->setSlug($slug);
                    $entityManagerInterface->persist($departement);
                    $entityManagerInterface->flush();
                    // } catch (\Throwable $th) {
                    //     // throw new Exception("Impossible d'importer ce fichier.");
                    //     $this->addFlash('error', "Impossible d'importer ce fichier.");
                    //     return $this->redirectToRoute('app_add_csv', [], Response::HTTP_SEE_OTHER);
                    // }
                } else {
                    $count = "1";
                }
            }
            $this->addFlash('success', 'Votre fichier a été importé avec succès');
            return $this->redirectToRoute('app_departement_index');
        }
        return $this->render('departement/add-csv.html.twig', [
            'form' => $form->createView()
            // 'erreur' => $erreur,
        ]);
    }
}
