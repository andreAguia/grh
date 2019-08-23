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
                     FROM tblicencapremio 
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
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega array com os dias publicados
        $select = 'SELECT idPublicacaoPremio
                     FROM tblicencapremio
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
                     FROM tblicencapremio 
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
    
    ###########################################################

    function get_publicacaoComDisponivelNegativo($idServidor){

    /**
     * Informe se o servidor tem alguma publicação com mais dias fruídos que publicados
     */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega as publicações desse servidor
        $select = 'SELECT idPublicacaoPremio
                     FROM tbpublicacaopremio
                    WHERE idServidor = '.$idServidor.'
                 ORDER BY dtInicioPeriodo';
        
        $result = $pessoal->select($select);
        
        # Percorre cada publicação para ver se tem dias disponíiveis
        foreach ($result as $publicacao){
            $dias = $this->get_numDiasDisponiveisPorPublicacao($publicacao[0]);
            if($dias < 0){
                # Retorna TRUE, ou seja, com problemas
                return TRUE;
                break;
            }
        }
        
        # Retorna FALSE, ou seja sem problemas
        return FALSE;
    }
    
    ########################################################### 

    function get_numPublicacoes($idServidor){

    /**
     * Informe o número de publicações de Licença Prêmio de um servidor
     */

        # Pega quantos dias foram publicados
        $select = 'SELECT idPublicacaoPremio
                     FROM tbpublicacaopremio 
                    WHERE idServidor = '.$idServidor;
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $row = $pessoal->count($select);
        return $row;
    }

    ########################################################### 

    function get_numPublicacoesPossiveis($idServidor){

    /**
     * Informe o número de publicações Possíveis de Licença Prêmio de um servidor, O número que ele deveria ter desde a data de admissão.
     */

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega o ano da Admissão
        $da = $pessoal->get_dtAdmissao($idServidor);
        $parte = explode("/",$da);
        $anoAdmissao = $parte[2];
        
        # Pega a ano atual
        $anoAtual = date("Y");
        
        # Calcula a quantidade de publicações possíveis
        $pp = intval(($anoAtual - $anoAdmissao) / 5);
        
        return $pp;
    }

    ########################################################### 

    function get_numPublicacoesFaltantes($idServidor){

    /**
     * Informe o número de publicações Que faltam ser publicadas.
     */

        # Pega publicações feitas 
        $pf = $this->get_numPublicacoes($idServidor);
        
        # Pega o número de Publicações Possíveis
        $pp = $this->get_numPublicacoesPossiveis($idServidor);
                
        # Calcula o número de publicações faltantes
        $pfalt = $pp - $pf;
        
        # Retorna o valor
        return $pfalt;
        
    }

    ###########################################################
    
    public function exibePublicacoesPremio($idServidor){
        
     /**
     * Exibe uma tabela com as publicações de Licença Prêmio de um servidor
     */
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os Dados 
        $numVinculos = $pessoal->get_numVinculosNaoAtivos($idServidor);
        $idSituacao = $pessoal->get_idSituacao($idServidor);
        $colunaDados = 3;


        # Cria os arrays da tabela
        $numProcesso = array("Processo");
        $diasPublicados = array("Dias Publicados");
        $diasFruidos = array("Dias Fruídos");
        $diasDisponiveis = array("Disponíveis");
        $cargo = array("Descrição");

        # Totais (quando tiver mais de um vinculo)
        $diasPublicadosTotal = 0;
        $diasFruidosTotal = 0;
        $diasDisponiveisTotal = 0;

        if(($numVinculos > 0) AND ($idSituacao == 1)){
            $colunaDados = 5;

            # Carrega um array com os idServidor de cada vinculo
            $vinculos = $pessoal->get_vinculos($idServidor);                    

            # Percorre os vinculos
            foreach($vinculos as $tt){

                # Pega o perfil da cada vínculo
                $idPerfilPesquisado = $pessoal->get_idPerfil($tt[0]);

                if($idServidor <> $tt[0]){
                    # Verifica se é estatutário
                    if($idPerfilPesquisado == 1){
                        $diasPublicados[] = $this->get_numDiasPublicados($tt[0]);
                        $diasFruidos[] = $this->get_numDiasFruidos($tt[0]);
                        $diasDisponiveis[] = $this->get_numDiasDisponiveis($tt[0]);
                        $numProcesso[] = $this->get_numProcesso($tt[0]);
                        $cargo[] = "Vínculo Anterior<br>".$pessoal->get_cargoSimples($tt[0]);

                        # Totais
                        $diasPublicadosTotal += $this->get_numDiasPublicados($tt[0]);
                        $diasFruidosTotal += $this->get_numDiasFruidos($tt[0]);
                        $diasDisponiveisTotal += $this->get_numDiasDisponiveis($tt[0]);
                    }
                }
            }
        }

        # Pega os dados do vinculo principal
        $diasPublicados[] = $this->get_numDiasPublicados($idServidor);
        $diasFruidos[] = $this->get_numDiasFruidos($idServidor);
        $diasDisponiveis[] = $this->get_numDiasDisponiveis($idServidor);
        $numProcesso[] = $this->get_numProcesso($idServidor);
        $cargo[] = "Vínculo Atual<br>".$pessoal->get_cargoSimples($idServidor);

         # Totais
        $diasPublicadosTotal += $this->get_numDiasPublicados($idServidor);
        $diasFruidosTotal += $this->get_numDiasFruidos($idServidor);
        $diasDisponiveisTotal += $this->get_numDiasDisponiveis($idServidor);

        if(($numVinculos > 0) AND ($idSituacao == 1)){
            $numProcesso[] = "";
            $diasPublicados[] = $diasPublicadosTotal;
            $diasFruidos[] = $diasFruidosTotal;
            $diasDisponiveis[] = $diasDisponiveisTotal;
            $cargo[] = "Total";
        }
            
        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna($colunaDados);
                        
            # Tabela
            $tabela = array($numProcesso,
                            $diasPublicados,
                            $diasFruidos,
                            $diasDisponiveis);

            $estatistica = new Tabela();
            $estatistica->set_conteudo($tabela);
            $estatistica->set_label($cargo);
            $estatistica->set_align(array("left"));
            #$estatistica->set_width(array(60,40));
            $estatistica->set_totalRegistro(FALSE);
            $estatistica->set_titulo("Dados");
            $estatistica->show();
        
        $grid->fechaColuna();
        $grid->abreColuna(12-$colunaDados);
                
        # Conecta com o banco de dados
        $pessoal = new Pessoal();
    
        # Exibe as Publicações
        $select = 'SELECT dtPublicacao,
                        dtInicioPeriodo,
                        dtFimPeriodo,
                        numDias,
                        idPublicacaoPremio,
                        idPublicacaoPremio,
                        idPublicacaoPremio
                   FROM tbpublicacaopremio
                   WHERE idServidor = '.$idServidor;
        
        # Inclui as publicações de outros vinculos
        if(($numVinculos > 0) AND ($idSituacao == 1)){
            # Percorre os vinculos
            foreach($vinculos as $tt){
                $select .= ' OR idServidor = '.$tt[0];
            }            
        }
        
        $select .= ' ORDER BY dtInicioPeriodo desc';

        $result = $pessoal->select($select);
        $count = $pessoal->count($select);

        # Cabeçalho da tabela
        $titulo = 'Publicações';
        $label = array("Data da Publicação","Período Aquisitivo <br/> Início","Período Aquisitivo <br/> Fim","Dias <br/> Publicados","Dias <br/> Fruídos","Dias <br/> Disponíveis");
        $width = array(15,10,15,15,15,10,10,10);
        $funcao = array('date_to_php','date_to_php','date_to_php');
        $classe = array(NULL,NULL,NULL,NULL,'LicencaPremio','LicencaPremio');
        $metodo = array(NULL,NULL,NULL,NULL,'get_numDiasFruidosPorPublicacao','get_numDiasDisponiveisPorPublicacao');
        $align = array('center');            

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($result);
        $tabela->set_align($align);
        $tabela->set_label($label);
        #$tabela->set_width($width);
        $tabela->set_titulo($titulo);
        $tabela->set_funcao($funcao);
        $tabela->set_classe($classe);
        $tabela->set_metodo($metodo);
        $tabela->set_numeroOrdem(TRUE);
        $tabela->set_numeroOrdemTipo("d");
        
        $tabela->set_formatacaoCondicional(array(array('coluna' => 5,
                                                       'valor' => 0,
                                                       'operador' => '<',
                                                       'id' => 'alerta')));

        $tabela->show();
        
        $grid->fechaColuna();
        $grid->fechaGrid();   
    }

###########################################################
    
    public function exibeProcedimentos(){
        
     /**
     * Exibe uma tabela com as publicações de Licença Prêmio de um servidor
     */
        
        # Inicia a classe de procedimentos
        $procedimento = new Procedimento();

        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(6);
        
        $procedimento->exibeProcedimento(11);
        
        $grid->fechaColuna();
        $grid->abreColuna(6);
        
        $procedimento->exibeProcedimento(12);
        
        $grid->fechaColuna();
        $grid->fechaGrid();   
    }

###########################################################
    
    public function exibeLicencaPremio($idServidor){
        
     /**
     * Exibe uma tabela com as Licença Prêmio de um servidor
     */
        # Conecta com o banco de dados
        $pessoal = new Pessoal();
        
        # Exibe as Publicações
        $select = 'SELECT tbpublicacaopremio.dtPublicacao,
                          tbpublicacaopremio.dtInicioPeriodo,
                          tbpublicacaopremio.dtFimPeriodo,
                          dtInicial,
                          tblicencapremio.numdias,
                          ADDDATE(dtInicial,tblicencapremio.numDias-1),
                          idLicencaPremio
                     FROM tblicencapremio LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                    WHERE tblicencapremio.idServidor = '.$idServidor.'
                 ORDER BY dtInicial desc';

        $result = $pessoal->select($select);
        $count = $pessoal->count($select);
        
        # Dados do vínculo
        $dtAdm = $pessoal->get_dtAdmissao($idServidor);
        $dtSai = $pessoal->get_dtSaida($idServidor);
        $motivo = $pessoal->get_motivo($idServidor);
        $cargo = $pessoal->get_cargo($idServidor);
        
        # Título
        $titulo  = "Licença Prêmio do Vínculo no Cargo $cargo:<br/>Admissão: $dtAdm - Saída: $dtSai ($motivo)";
        
        if($count > 0){

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_titulo($titulo);
            $tabela->set_conteudo($result);
            $tabela->set_label(array("Data da Publicaçãod","Período Aquisitivo<br/>Início","Período Aquisitivo<br/>Fim","Inicio","Dias","Término"));        
            $tabela->set_align(array("center"));
            $tabela->set_funcao(array('date_to_php','date_to_php','date_to_php','date_to_php',NULL,'date_to_php'));
            $tabela->set_numeroOrdem(TRUE);
            $tabela->set_numeroOrdemTipo("d");
            $tabela->set_exibeTempoPesquisa(FALSE);
            $tabela->show();
            
        }else{
            
            br();
            tituloTable($titulo);
            $callout = new Callout();
            $callout->abre();
                p('Nenhum item encontrado !!','center');
            $callout->fecha();
        }        
    }

###########################################################

}