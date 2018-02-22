<?php
class LicencaPremio{
 /**
  * Exibe as informações sobre a licençca prêmio de um servidor
  * 
  * @author André Águia (Alat) - alataguia@gmail.com
  * 
  * @var private $anoExercicio   integer NULL O Ano de exercícios das férias
  * @var private $lotacao        integer NULL O id da lotação. Quando NULL exibe de todas a universidade
  * @var private $permiteEditar  boolean TRUE Indica se terá botão para acessar informções dos servidores
  */
    
    private $idServidor = NULL;
    
    ###########################################################
    
    public function __construct($idServidor){
                
    /**
     * Inicia a classe informando o id do servidor
     * 
     * @param $idServidor integer NULL O id do Servidor
     */    
    
        $this->idServidor = $idServidor;
    }
        
    ###########################################################    
    
    function get_NumDiasFruidos(){

    /**
     * Informa a quantidade de dias fruídos
     */
        
        # Pega quantos dias foram fruídos
        $select = 'SELECT SUM(numDias) 
                     FROM tblicencaPremio 
                    WHERE idServidor = '.$this->idServidor;

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);

        # Retorno
        if (is_null($row[0]))
            return 0;
        else 
            return $row[0];
    }

    ########################################################### 

    function get_NumDiasPublicada(){

    /**
     * Informe o número de dias publicados
     */

        # Pega quantos dias foram publicados
        $select = 'SELECT SUM(numDias) 
                     FROM tbpublicacaopremio 
                    WHERE idServidor = '.$this->idServidor;
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);
        
        # Retorno
        if (is_null($row[0]))
            return 0;
        else 
            return $row[0];
    }

    ###########################################################

    ###########################################################     
}