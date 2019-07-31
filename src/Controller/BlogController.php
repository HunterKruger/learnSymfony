<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Articles;
use App\Form\ArticleType;
use App\Form\CommentType;

use App\Repository\ArticlesRepository;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class BlogController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(){
        return $this->render('/blog/home.html.twig',[
            'controllerName'=>"BlogController",
            'greeting'=>"How are you, my friend?",
            'gender'=>"female"
        ]);
    }

    /**
     * @Route("/articles", name="articles")
     */
    public function articles(ArticlesRepository $repo)
    {
        //$repo=$this->getDoctrine()->getRepository(Articles::class);
        $articles=$repo->findAll();
        return $this->render('/blog/articles.html.twig',[
            'controllerName'=>"BlogController",
            'articles'=>$articles
        ]);
    }

    /**
     * @Route("/myexperiences", name="myexperiences")
     */
    public function myexperiences(){
        return $this->render('/blog/myexperiences.html.twig',[
            'controllerName'=>"BlogController"
        ]);
    }

    /**
     * @Route("/createArticle", name="createArticle")
     * @Route("/editArticle/{id}", name="editArticle")
     */
    //two routes
    //1st for create a new article, 2nd for edit an existing article
    public function createArticle(Articles $article=null, Request $request, ObjectManager $manager){
       
        if(!$article){
            $article=new Articles();
        }

        // $form=$this->createFormBuilder($article)
        //                          ->add('title')
        //                          ->add('content')
        //                          ->add('image')
        //                          ->getForm();
        //no need to use, because we can call ArticleType instead

        $form=$this->createForm(ArticleType::class,$article);

        $form->handleRequest($request);
        dump($article);   //like printf in C, for testing

        if($form->isSubmitted() && $form->isValid()){
            if(!$article->getId()){
                $article->setCreatedAt(new \DateTime()); 
            }
             $manager->persist($article);
             $manager->flush();
             return $this->redirectToRoute('readmore',['id'=>$article->getId()]);             //go to the newly created article page!
        }

        return $this->render('/blog/createArticle.html.twig',[
            'controllerName'=>"BlogController",
            'formArticle'=>$form->createView(),
            'editMode'=>$article->getId()!==null                              //editMode=1 when an article has an Id
        ]);
    }
    //put this function before readmore function to evade confusion of route

     /**
     * @Route("/readmore/{id}", name="readmore")                
     */
    public function readmore(Articles $article, Request $request, ObjectManager $manager){
        //$repo=$this->getDoctrine()->getRepository(Articles::class);
        //$article=$repo->find($id);
        $comment=new Comment();
        $form=$this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $comment->setCreatedAt(new \DateTime())
                                    ->setArticle($article);

            $manager->persist($comment);
            $manager->flush();
            return $this->redirectToRoute('readmore',['id'=>$article->getId()]);           
        }
        return $this->render('/blog/readmore.html.twig',[
            'article'=>$article,
            'formComment'=>$form->createView()
        ]);
    }




}
