<?php
class Acumulacao{
 /**
  * Abriga as várias rotina referentes ao afastamento do servidor
  *
  * @author André Águia (Alat) - alataguia@gmail.com
  */



    ###########################################################

    /**
    * Método Construtor
    */
    public function __construct(){

    }
  
    ###########################################################

    function get_dados($idAcumulacao){

    /**
     * Informa os dados da base de dados
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if(vazio($idAcumulacao)){
            alert("É necessário informar o id da Acumulação.");
            return;
        }

        # Pega os dados
        $select = 'SELECT * 
                     FROM tbacumulacao
                    WHERE idAcumulacao = '.$idAcumulacao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);

        # Retorno
        return $row;
    }

    ###########################################################

    function get_resultado($idAcumulacao){

    /**
     * Informa o resultado final de uma acumulação
     */
        # Inicia a variável de retorno
        $retorno = NULL;
        
        # Pega os dados
        $dados = $this->get_dados($idAcumulacao);
        
        # Joga os resultados nas variáveis
        $resultado = $dados["resultado"];
        $resultado1 = $dados["resultado1"];
        $resultado2 = $dados["resultado2"];
        $resultado3 = $dados["resultado3"];
        
        # Verifica o primeiro resultado
        if(!vazio($resultado)){
            $retorno = $resultado;
        }
        
        # Verifica o primeiro recurso
        if(!vazio($resultado1)){
            $retorno = $resultado1;
        }
        
        # Verifica o segundo recurso
        if(!vazio($resultado2)){
            $retorno = $resultado2;
        }
        
        # Verifica o último recurso
        if(!vazio($resultado3)){
            $retorno = $resultado3;
        }
        
        # Trata o retorno
        if($retorno == 1){
            $retorno = "Lícito";
        }elseif($retorno == 2){
            $retorno = "Ilícito";
        }else{
            $retorno = "Em Aberto";
        }
        
        return $retorno;
    }

    ###########################################################

}
