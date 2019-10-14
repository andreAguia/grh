<?php
class Concurso
{
 /**
  * Abriga as várias rotina referentes a concurso
  *
  * @author André Águia (Alat) - alataguia@gmail.com
  * 
  * @var private $idConcurso integer NULL O id do concurso
  */
    
    private $idConcurso = NULL;

##############################################################

    public function __construct($idConcurso = NULL){
    /**
     * Inicia a Classe somente
     * 
     * @param $idConcurso integer NULL O id do concurso
     * 
     * @syntax $concurso = new Concurso([$idConcurso]);
     */
        
        $this->idConcurso = $idConcurso;
    }
  
##############################################################

    public function get_dados($idConcurso = NULL){

    /**
     * Informa os dados da base de dados
     * 
     * @param $idConcurso integer NULL O id do concurso
     * 
     * @syntax $concurso->get_dados([$idConcurso]);
     */
        
        # Joga o valor informado para a variável da classe
        if(!vazio($idConcurso)){
            $this->idConcurso = $idConcurso;
        }
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if(vazio($this->idConcurso)){
            alert("É necessário informar o id do Concurso.");
            return;
        }

        # Pega os dados
        $select = 'SELECT * 
                     FROM tbconcurso
                    WHERE idConcurso = '.$this->idConcurso;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);

        # Retorno
        return $row;
    }

##############################################################

}
