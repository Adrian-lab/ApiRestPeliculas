<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use App\Form\PeliculaType;
class ClienteController extends AbstractController
{
    private $Api = "http://127.0.0.1:8000/api/";
    /**
     * @Route("/", name="lista_peliculas")
     */
    public function index()
    {
        $client = HttpClient::create();
        $response = $client->request('GET', $this->Api.'peliculas');

        $content = $response->toArray();
        return $this->render('cliente/index.html.twig', [
            'peliculas' => $content,
        ]);
    }
    /**
     * @Route("/pelicula/anyadir/", name="pelicula_anyadir")
     */
    public function peliculaAnyadir(Request $request)
    {
        $defaultData = ['message' => 'Type your message here'];
        $form = $this->createFormBuilder($defaultData)
            ->add('nombre', TextType::class)
            ->add('genero', TextType::class)
            ->add('descripcion', TextareaType::class)
            ->add('send', SubmitType::class)
            ->getForm();        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $form->getData();

            $client = HttpClient::create();
            $response = $client->request('POST', $this->Api.'pelicula',[
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => ['nombre' => $form->getData()->getNombre(),
                            'genero' => $form->getData()->getGenero(),
                            'descripcion' => $form->getData()->getDescripcion()
                ],
            ]);

            return $this->redirectToRoute('lista_peliculas');
        }
        return $this->render('cliente/formularioPelicula.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/pelicula/modificar/{id}", name="pelicula_modificar")
     */
    public function peliculaModificar($id,Request $request)
    {
        $client = HttpClient::create();
        $response = $client->request('GET', $this->Api.'pelicula/'.$id);
        $content = $response->toArray();
        $form = $this->createFormBuilder($defaultData)
            ->add('nombre', $content['nombre'])
            ->add('genero', $content['genero'])
            ->add('descripcion', $content['descripcion'])
            ->add('send', SubmitType::class)
            ->getForm();        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $client = HttpClient::create();
            $response = $client->request('PUT', $this->Api.'pelicula/'.$content['id'],[
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => ['nombre' => $form->getData()->getNombre(),
                            'genero' => $form->getData()->getGenero(),
                            'descripcion' => $form->getData()->getDescripcion()
                ],
            ]);

            return $this->redirectToRoute('lista_peliculas');
        }
        return $this->render('cliente/formularioPelicula.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    /**
     * @Route("/pelicula/{id}", name="pelicula_detalle")
     */
    public function peliculaDetalle($id)
    {
        $client = HttpClient::create();
        $response = $client->request('GET', $this->Api.'pelicula/'.$id);

        $content = $response->toArray();
        return $this->render('cliente/perfilPelicula.html.twig', [
            'pelicula' => $content,
        ]);
    }
    /**
     * @Route("/pelicula/borrar/{id}", name="pelicula_borrar")
     */
    public function peliculaBorrar($id)
    {
        $client = HttpClient::create();
        $response = $client->request('DELETE', $this->Api.'pelicula/'.$id);

        $statusCode = $response->getStatusCode();
        if($statusCode == 200){

            $response = $client->request('GET', $this->Api.'peliculas');
            $content = $response->toArray();
            return $this->render('cliente/index.html.twig', [
                'peliculas' => $content,
            ]);
        }else{

            $response = $client->request('GET', $this->Api.'pelicula/'.$id);
            $content = $response->toArray();
            return $this->render('cliente/perfilPelicula.html.twig', [
                'pelicula' => $content,
            ]);
        } 
    }
}
