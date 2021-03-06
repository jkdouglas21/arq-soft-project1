<?php
namespace Library\Database;

final class Repository
{   

    private $activeRecord;

    function __construct($class)
    {
        $this->activeRecord = $class;
    }

    //injeção de dependencia
    function load(Criteria $criteria)
    {
        //instancia a instrução de SELECT
        $reflection = new \ReflectionClass($this->activeRecord);

        $sql = "SELECT * FROM " . constant($this->activeRecord.'::TABLENAME'); //nome da classe::constante = nome tabela no bd
 
        //Obtêm a cláusula  WHERE da classe criteria.
        if($criteria){
            $expression = $criteria->dump();//resultado da expressão de filter 
            if($expression){
                $sql .= ' WHERE ' . $expression;
            }

            //Obtêm as propriedades do criterio
            $order = $criteria->getProperty('order');
            $limit = $criteria->getProperty('limit');
            $offset = $criteria->getProperty('offset');

            //Obtêm a ordenação do SELECT
            if($order){
                $sql .= ' ORDER BY ' . $order;
            }
            if($limit){
                $sql .= ' LIMIT ' . $limit;
            }
            if($offset){
                $sql .= ' OFFSET ' . $offset;
            }
        }

        //obtêm a transação ativa
        if($conn = Transaction::get()){
            Transaction::log($sql); //registra mensagem de log
            //executa consulta ao banco de dados
            $result = $conn->query($sql);
            $results = array();
            if ($result){
                //percorre o resultado da consulta retornando um objeto
                while ($row = $result->fetchObject($this->activeRecord)) {
                    //armazena no array results
                    $results[] = $row;
                }
            }
            return $results;
        }
        else {
            throw new \Exception("Não há conexão ativa");            
        }
    }

    function delete(Criteria $criteria)
    {
        $expression = $criteria->dump();
        $sql = "DELETE FROM " . constant($this->activeRecord.'::TABLENAME');
        if($expression){
            $sql .= ' WHERE ' . $expression;
        }
        //obtem transação ativa
        if($conn = Transaction::get()){
            Transaction::log($sql); // registra mensagem de log
            $result = $conn->exec($sql);//executa a instrção de delete
            return $result;
        }
        else {
            throw new \Exception("Não há conexão ativa");            
        }
    }

    function count(Criteria $criteria)
    {
        $expression = $criteria->dump();
        $sql = "SELECT COUNT(*) FROM " . constant($this->activeRecord.'::TABLENAME');
        if($expression){
            $sql .= ' WHERE ' . $expression;
        }

        //obtem conextão ativa
        if($conn = Transaction::get()){
            Transaction::log($sql);
            $result = $conn->query($sql);
            if($result){
                $row = $result->fetch();
            }
            return $row[0];
        }
        else{
            throw new \Exception("Não há conexão ativa");            
        }
    }
}