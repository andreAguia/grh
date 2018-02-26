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
                     FROM tblicencapremio 
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

        # Pega o idServidor dessa Licença
        $idServidor = $this->get_idServidorPorLicenca($idLicencaPremio);
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega array com os dias publicados
        $select = 'SELECT idPublicacaoPremio
                     FROM tbpublicacaopremio 
                    WHERE idServidor = '.$idServidor;
        
        $publicados = $pessoal->select($select);
        
        # Pega array com os dias fruídos
        $select = 'SELECT idLicencaPremio,
                          numDias 
                     FROM tblicencapremio 
                    WHERE idServidor = '.$idServidor.' 
                 ORDER BY dtInicial';       
                        
        $fruidos = $pessoal->select($select);
        
        # Rotina que informa (matematicamente) a publicação de uma licença
        $somador = 0; // Zera o somador
        
        # Percorre as licenças somando os dias e dividindo em grupos de 90 dias
        foreach($fruidos as $arrayLicenca){
            $somador += $arrayLicenca[1];   // soma
            
            # Verifica se é a licença solicitada
            if($arrayLicenca[0] == $idLicencaPremio){
                $divisao = ($somador/90);        // divide por 90
                $indice = (int)($divisao - 0.01);  // acerta para que o valor fique perfeito para ser um índice de array
                $idRetornado = $publicados[$indice][0]; // descobre a id da publicação.
            }
        }
                        
        # Pega os dados dessa publicação
        $dados = $this->get_dadosPublicacao($idRetornado);
        
        $retorno = "Publicado em:".date_to_php($dados[0])." (".date_to_php($dados[1])." - ".date_to_php($dados[2]).")";
        
        # Retorno
        return $retorno;
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
        # Pega o idServidor dessa Licença
        $idServidor = $this->get_idServidorPorPublicacao($idPublicacaoPremio);
        
        $diasPublicados = $this->get_numDiasPublicados($idServidor);
        $diasFruidos = $this->get_numDiasFruidos($idServidor);
        $diasDisponiveis = $this->get_numDiasDisponiveis($idServidor);
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega array com os dias publicados
        $select = 'SELECT idPublicacaoPremio
                     FROM tbpublicacaopremio 
                    WHERE idServidor = '.$idServidor;
        
        $publicados = $pessoal->select($select);
        
        # Pega array com os dias fruídos
        $select = 'SELECT idLicencaPremio,
                          numDias 
                     FROM tblicencapremio 
                    WHERE idServidor = '.$idServidor.' 
                 ORDER BY dtInicial';       
                        
        $fruidos = $pessoal->select($select);
        
        # Zera os somatórios
        $somador = 0;
        $somaPublic = 0; 
        
        # Percorre as licenças somando os dias e dividindo em grupos de 90 dias
        foreach($fruidos as $arrayLicenca){
            $somador += $arrayLicenca[1];   // soma
            
            $divisao = ($somador/90);        // divide por 90
            $indice = (int)($divisao - 0.01);  // acerta para que o valor fique perfeito para ser um índice de array
            $idPublica = $publicados[$indice][0]; // descobre a id da publicação.
            if($idPublica == $idPublicacaoPremio){
                $somaPublic += $arrayLicenca[1];
            }
        }
        
        # Retorna
        return $somaPublic;
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
    
}