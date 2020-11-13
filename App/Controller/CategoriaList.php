<?php

namespace App\Controller;

use App\Model\Categoria;
use Library\Control\Page;
use Library\Database\Criteria;
use Library\Database\Repository;
use Library\Database\Transaction;

class CategoriaList extends Page
{

    public function __construct() 
    {
        parent::__construct();
        $this->template = $this->twig->load('categoria-list.html');
    }

    public function index()
    {
        $data = array();
        try {
            Transaction::open('self_menu');
            
            $repository = new Repository(Categoria::class);
            $criteria = new Criteria();
            $criteria->setProperty('order', 'id desc');
            $objects = $repository->load($criteria);
            $data = ['categories' => $objects, 'title' => 'Categorias'];
            echo $this->template->render($data);

            Transaction::close();
        } catch (\Exception $e) {
            Transaction::rollback();
            echo json_encode(["status" => "error", "data" => message($e->getMessage(),'danger',true)]);
        }        
    }

    public function create($data): void
    {
        $response = new \stdClass;
        try {
            $catData = filter_var_array($data, FILTER_SANITIZE_STRING);

            if (in_array("", $catData)) {
                $response->status = 'error';
                $response->data = message(
                    'Preencha os campos para criar uma nova categoria', 'danger', true
                );
                echo json_encode($response);
                return;
            }
            
            Transaction::open('self_menu');

            $categoria = new Categoria();
            $categoria->fromArray($catData);
            $categoria->store();

            Transaction::close();

            $response->status = 'success';
            $response->message = message('Categoria criada com sucesso!', 'success', true);
            $response->data = $this->twig->load('categoria.html')->render( ['category' => $categoria] );
            
            echo json_encode($response);

        } catch (\Exception $e) {
            Transaction::rollback();
            echo json_encode(["status" => "error", "data" => message($e->getMessage(),'danger',true)]);
        }
    }

    public function changeStatus(array $param)
    {      

        try {

            if (empty($param['id'])) {
                return;
            }
            $id = filter_var($param['id'], FILTER_VALIDATE_INT);
            $status = filter_var($param['status'], FILTER_VALIDATE_BOOLEAN);

            Transaction::open('self_menu');

            $item = Categoria::find($id);            

            if ($item) {
                $item->ativo = $status ? "1" : "0";
                $item->store();
            }
               
            Transaction::close();

            echo json_encode(['status' => 'success', 'data' => $item->toArray()]);
            return;
            
        } catch (\Exception $e) {
            Transaction::rollback();
            echo json_encode(["status" => "error", "data" => message($e->getMessage(),'danger',true)]);
        }
    }

}