<?php
class Progressao{
 /**
  * Abriga as várias rotina do Cadastro de progressão e enquadramento de um servidor
  * 
  * @author André Águia (Alat) - alataguia@gmail.com  
  */
    
    private $idServidor = NULL;

    ##############################################################
    
    /**
    * Método Construtor
    */
    public function __construct($idServidor = NULL){
        
        $this->idServidor = $idServidor;
    }

    ###########################################################
    
    function get_dados($idProgressao){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT *
                   FROM tbprogressao
                  WHERE idProgressao = $idProgressao";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        return $dados;
    }
    
    ###########################################################
    
    function get_IdClasseAtual($idServidor = NULL){
        
        /**
         * Fornece o idClasse atual do servidor
         */
        
        # Troca o valor informado para a variável da classe
        if(!vazio($idServidor)){
            $this->idServidor = $idServidor;
        }
        
        $select = "SELECT idClasse
                     FROM tbprogressao
                    WHERE idServidor = $this->idServidor
                 ORDER BY dtInicial desc";
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);

        return $row[0];
    }
    
    ###########################################################
    
    function get_dtInicialAtual($idServidor){
        
        /**
         * Fornece a data Inicial da progressão atual do servidor
         */
        
        # Troca o valor informado para a variável da classe
        if(!vazio($idServidor)){
            $this->idServidor = $idServidor;
        }
        
        $select = "SELECT dtInicial
                     FROM tbprogressao
                    WHERE idServidor = $this->idServidor
                 ORDER BY dtInicial desc";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        return date_to_php($row[0]);
    }
    
    ###########################################################
    
    function analisaServidor($idServidor){
        
        /**
         * Fornece a data Inicial da progressão atual do servidor
         */
        
        # Troca o valor informado para a variável da classe
        if(!vazio($idServidor)){
            $this->idServidor = $idServidor;
        }
        
        # Pega o idClasse do servidor
        $idClasse = $this->get_IdClasseAtual();
        
        # Pega o nível do cargo desse servidor
        $pessoal = new Pessoal();
        $nivel = $pessoal->get_nivelCargo($this->idServidor);
        
        # Pega o idCLasse da última classe possível do plano de cargos vigente para esse servidor
        $plano = new PlanoCargos();
        $idClasseUltimo = $plano->ultimoIdClasse($nivel);
        
        # Analisa se o servidor está na última classe possível
        if($idClasse == $idClasseUltimo){
            $analise = "Não Pode Progredir";
        }else{
            $analise = "Pode Progredir";
        }
        
        return $analise;
    }
    
    ###########################################################
}