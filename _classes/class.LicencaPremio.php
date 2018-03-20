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
     * Inicia a classe 
     */    
    
        
    }
        
    ###########################################################    
    
    function get_numDiasFruidos($idServidor){

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

    function get_numDiasPublicados($idServidor){

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

    function get_numDiasDisponiveis($idServidor){

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

    function get_idServidorPorPublicacao($idPublicacaoPremio){

    /**
     * Informe o idServidor de uma publicação
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

    function get_idServidorPorLicenca($idLicencaPremio){

    /**
     * Informe o idServidor de uma licença
     */

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega array com os dias publicados
        $select = 'SELECT idServidor
                     FROM tblicencaPremio 
                    WHERE idLicencaPremio = '.$idLicencaPremio;
        
       $row = $pessoal->select($select,FALSE);
        
        # Retorno
        return $row[0];
    }

    ###########################################################                          

    function get_publicacao($idLicencaPremio){

    /**
     * Informe a publicação de uma licença
     */
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega array com os dias publicados
        $select = 'SELECT idPublicacaoPremio
                     FROM tblicencaPremio
                    WHERE idLicencaPremio = '.$idLicencaPremio;
        
        $retorno = $pessoal->select($select,FALSE);
        
        return $retorno[0];
    }

    ###########################################################
    
    function get_dadosPublicacao($idPublicacaoPremio){

    /**
     * Informe a data e o período aquisitivo
     */

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega array com os dias publicados
        $select = 'SELECT dtPublicacao,
                          dtInicioPeriodo,
                          dtFimPeriodo
                     FROM tbpublicacaopremio 
                    WHERE idPublicacaoPremio = '.$idPublicacaoPremio;
        
        $retorno = $pessoal->select($select,FALSE);
        
        # Retorno
        return $retorno;
    }

    ###########################################################

    function get_numDiasFruidosPorPublicacao($idPublicacaoPremio){

    /**
     * Informe o número de dias fruídos em uma Publicação
     */
       
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        #  Pega quantos dias foram fruídos
        $select = 'SELECT SUM(numDias) 
                     FROM tblicencaPremio 
                    WHERE idPublicacaoPremio = '.$idPublicacaoPremio;
                        
        $fruidos = $pessoal->select($select,FALSE);
        
        # Retorna
        return $fruidos[0];
    }

    ###########################################################

    function get_numDiasDisponiveisPorPublicacao($idPublicacaoPremio){

    /**
     * Informe o número de dias disponíveis em uma Publicação
     */
        # Pega os dias publicados
        $numDiasPublicados = $this->get_numDiasPublicadosPorPublicacao($idPublicacaoPremio);
        
        # Pega os dias fruídos
        $numDiasFruidos = $this->get_numDiasFruidosPorPublicacao($idPublicacaoPremio);
        
         # Retorno
        return $numDiasPublicados - $numDiasFruidos;
    }
    
    ###########################################################

    function get_numDiasPublicadosPorPublicacao($idPublicacaoPremio){

    /**
     * Informe o número de dias publicados
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT numDias
                     FROM tbpublicacaopremio 
                    WHERE idPublicacaoPremio = '.$idPublicacaoPremio;
        
        $retorno = $pessoal->select($select,FALSE);
        
        # Retorno
        return $retorno[0];
    }
    
    ###########################################################

    function get_numProcesso($idServidor){

    /**
     * Informe o número do processo da licença prêmio de um servidor
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os dias publicados
        $select = 'SELECT processoPremio
                     FROM tbservidor
                    WHERE idServidor = '.$idServidor;
        
        $retorno = $pessoal->select($select,FALSE);
        
        # Retorno
        return $retorno[0];
    }
    
    ###########################################################

    function get_proximaPublicacaoDisponivel($idServidor){

    /**
     * Informe a primeira publicação de licença prêmio com dias disponíveis
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega as publicações desse servidor
        $select = 'SELECT idPublicacaoPremio, 
                          date_format(dtPublicacao,"%d/%m/%Y")
                     FROM tbpublicacaopremio
                    WHERE idServidor = '.$idServidor.'
                 ORDER BY dtInicioPeriodo';
        
        $result = $pessoal->select($select);
        
        # Percorre cada publicação para ver se tem dias disponíiveis
        foreach ($result as $publicacao){
            $dias = $this->get_numDiasDisponiveisPorPublicacao($publicacao[0]);
            if($dias > 0){
                return array(array($publicacao[0],$publicacao[1]));
                break;
            }
        }
    }
}