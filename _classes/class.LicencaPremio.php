<?php
class LicencaPremio{
 /**
  * Exibe as informações sobre a licençca prêmio
  * 
  * @author André Águia (Alat) - alataguia@gmail.com
  * 
  */
    
    ###########################################################
    
    public function __construct(){
                
    /**
     * Inicia a classe informando o id do servidor
     */    
    
        
    }
        
    ###########################################################    
    
    function get_NumDiasFruidos($idServidor){

    /**
     * Informa a quantidade de dias fruídos
     */
        
        # Pega quantos dias foram fruídos
        $select = 'SELECT SUM(numDias) 
                     FROM tblicencaPremio 
                    WHERE idServidor = '.$idServidor;

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

    function get_NumDiasPublicados($idServidor){

    /**
     * Informe o número de dias publicados
     */

        # Pega quantos dias foram publicados
        $select = 'SELECT SUM(numDias) 
                     FROM tbpublicacaopremio 
                    WHERE idServidor = '.$idServidor;
        
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

    function get_NumDiasDisponiveis($idServidor){

    /**
     * Informe o número de dias disponíveis
     */

        $diasPublicados = $this->get_NumDiasPublicados($idServidor);
        $diasFruidos = $this->get_NumDiasFruidos($idServidor);
        $diasDisponiveis = $diasPublicados - $diasFruidos;
        
        # Retorno
        return $diasDisponiveis;
    }

    ###########################################################

    function get_NumDiasDisponiveisPorPublicacao($idPublicacaoPremio){

    /**
     * Informe o número de dias disponíveis em uma Publicação
     */

        # Pega o idServidor dessa Publicação
        $idServidor = $this->get_idServidorPorPublicacao($idPublicacaoPremio);
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega array com os dias publicados
        $select = 'SELECT idPublicacaoPremio,
                          numDias 
                     FROM tbpublicacaopremio 
                    WHERE idServidor = '.$idServidor;
        
       
        $publicados = $pessoal->select($select);
        $publicacoes = $pessoal->count($select);
        
        # Pega array com os dias fruídos
        $select = 'SELECT idLicencaPremio,
                          numDias 
                     FROM tblicencapremio 
                    WHERE idServidor = '.$idServidor;
        
        $fruidos = $pessoal->select($select);
        
        # Pega os somtórios
        $diasPublicados = $this->get_NumDiasPublicados($idServidor);
        $diasFruidos = $this->get_NumDiasFruidos($idServidor);
        $diasDisponiveis = $this->get_NumDiasDisponiveis($idServidor);
        
        
        
        # Retorno
        return $publicacoes;
    }

    ###########################################################

    function get_NumDiasFruidosPorPublicacao($idPublicacaoPremio){

    /**
     * Informe o número de dias fruídos em uma Publicação
     */

              
        
        # Retorno
        return $idPublicacaoPremio;
    }

    ###########################################################

    function get_idServidorPorPublicacao($idPublicacaoPremio){

    /**
     * Informe o idServidor de uma Publicação
     */

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega array com os dias publicados
        $select = 'SELECT idServidor
                     FROM tbpublicacaopremio 
                    WHERE idPublicacaoPremio = '.$idPublicacaoPremio;
        
       $row = $pessoal->select($select,FALSE);
        
        # Retorno
        return $row[0];
    }

    ###########################################################             
}