<?php
namespace App\Controller;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Entity\CategorySearch;
use App\Form\CategorySearchType;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Entity\PropertySearch;
use App\Form\PropertySearchType;
use App\Entity\PriceSearch;
use App\Form\PriceSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;






class IndexController extends AbstractController
{
/**
 *@Route("/",name="article_list")
 */
public function home(EntityManagerInterface $entityManager,Request $request)
 {
    $propertySearch = new PropertySearch();
    $form = $this->createForm(PropertySearchType::class,$propertySearch);
    $form->handleRequest($request);
    //initialement le tableau des articles est vide,
    //c.a.d on affiche les articles que lorsque l'utilisateur
    //clique sur le bouton rechercher
    $articles= [];
 
    if($form->isSubmitted() && $form->isValid()) {
    $nom = $propertySearch->getNom(); 
    if ($nom!="")
    //si on a fourni un nom d'article on affiche tous les articles ayant ce nom
    $articles = $entityManager->getRepository(Article::class)->findBy(['nom' => $nom] );
    else 
      //récupérer tous les articles de la table article de la BD
      // et les mettre dans le tableau $articles
      $articles = $entityManager->getRepository(Article::class)->findAll();}
      return $this->render('articles/index.html.twig', [
        'form' => $form->createView(),  // Ajout du formulaire ici
        'articles' => $articles]);
 }

 //function ajout:
    /**
  * @Route("/article/save")
  */
    public function save(EntityManagerInterface $entityManager) {
   //$entityManager = $this->getDoctrine()->getManager();
    $article = new Article();
    $article->setNom('Article 8');
    $article->setPrix(8000);
  
    $entityManager->persist($article);
      $entityManager->flush();
    return new Response('Article enregisté avec id '.$article->getId());
    }


/**
 * @Route("/article/new", name="new_article")
 * Method({"GET", "POST"})
 */
public function new(Request $request, EntityManagerInterface $entityManager)
{
    $article = new Article();
    $form = $this->createForm(ArticleType::class, $article);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $article = $form->getData();
        $entityManager->persist($article);
        $entityManager->flush();

        return $this->redirectToRoute('article_list');
    }

    return $this->render('articles/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

//fonction details:
/**
 * @Route("/article/{id}", name="article_show")
 */
public function show($id, EntityManagerInterface $entityManager): Response
    {
        $article = $entityManager->getRepository(Article::class)->find($id);
        if (!$article) {
            throw $this->createNotFoundException('L\'article n\'existe pas');
        }
        return $this->render('articles/show.html.twig',array('article' => $article));
        
    }

//function modif : 
/**
 * @Route("/article/edit/{id}", name="edit_article")
 * Method({"GET", "POST"})
 */
public function edit(Request $request, $id, EntityManagerInterface $entityManager)
    {
        // Récupération de l'article via l'EntityManager
        $article = $entityManager->getRepository(Article::class)->find($id);

        if (!$article) {
            throw $this->createNotFoundException('Aucun article trouvé pour cet id ' . $id);
        }

        // Création du formulaire avec les données de l'article récupéré
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Aucune méthode persist() nécessaire pour l'édition, car l'entité est déjà gérée
            $entityManager->flush();

            return $this->redirectToRoute('article_list');
        }

        return $this->render('articles/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

//function supp : 
/**
     * @Route("/article/delete/{id}", name="delete_article", methods={"DELETE"})

 */
public function delete(Request $request, $id, EntityManagerInterface $entityManager) {
   $article = $entityManager->getRepository(Article::class)->find($id);

   $entityManager->remove($article);
   $entityManager->flush();
  
   $response = new Response();
   $response->send();
   return $this->redirectToRoute('article_list');
   }


   /**
 * @Route("/category/newCat", name="new_category")
 * Method({"GET", "POST"})
 */
public function newCategory(Request $request) {
    $category = new Category();
    $form = $this->createForm(CategoryType::class,$category);
    $form->handleRequest($request);
    if($form->isSubmitted() && $form->isValid()) {
    $article = $form->getData();
    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->persist($category);
    $entityManager->flush();
    }
   return $this->render('articles/newCategory.html.twig',['form'=>
   $form->createView()]);
    }

    /**
 * @Route("/art_cat/", name="article_par_cat")
 * Method({"GET", "POST"})
 */
 public function articlesParCategorie(Request $request,EntityManagerInterface $entityManager) {
    $categorySearch = new CategorySearch();
    $form = $this->createForm(CategorySearchType::class,$categorySearch);
    $form->handleRequest($request);
    $articles= [];
    if($form->isSubmitted() && $form->isValid()) {
        $category = $categorySearch->getCategory();
        
        if ($category!="")
       $articles= $category->getArticles();
        else 
        $articles= $this->$entityManager()->getRepository(Article::class)->findAll();
        }
        
        return $this->render('articles/articlesParCategorie.html.twig',['form' => $form->createView(),'articles' => $articles]);
        }

        /**
     * @Route("/art_prix/", name="article_par_prix")
     * Method({"GET"})
     */
    /**
 * @Route("/art_prix/", name="article_par_prix")
 * Method({"GET"})
 */

public function articlesParPrix(Request $request, EntityManagerInterface $entityManager)
{
    $priceSearch = new PriceSearch();
    $form = $this->createForm(PriceSearchType::class, $priceSearch);
    $form->handleRequest($request);

    $articles = [];

    if ($form->isSubmitted() && $form->isValid()) {
        $minPrice = $priceSearch->getMinPrice();
        $maxPrice = $priceSearch->getMaxPrice();

        // Utilisez EntityManager pour récupérer les articles dans la plage de prix
        $articles = $entityManager->getRepository(Article::class)->findByPriceRange($minPrice, $maxPrice);
    }

    return $this->render('articles/articlesParPrix.html.twig', [
        'form' => $form->createView(),
        'articles' => $articles,
    ]);
}

}