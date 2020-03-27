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
    
    function get_numDiasFruidosTotal($idServidor){

    /**
     * Informa a quantidade de dias fruídos em todos os vinculos estatutários
     */
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega o idPessoa
        $idPessoa = $pessoal->get_idPessoa($idServidor);
    
        # Pega quantos dias foram fruídos
        $select = "SELECT SUM(numDias) 
                     FROM tblicencapremio LEFT JOIN tbservidor USING (idServidor)
                                          LEFT JOIN tbpessoa USING (idPessoa)
                    WHERE idPessoa = $idPessoa
                      AND tbservidor.idPerfil = 1";

        # Pega os valores
        $row = $pessoal->select($select,FALSE);

        # Retorno
        if (is_null($row[0]))
            return 0;
        else 
            return $row[0];
    }

    ########################################################### 

    function get_numDiasPublicadosTotal($idServidor){

    /**
     * Informe o número de dias publicados em todos os vinculos
     */
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega o idPessoa
        $idPessoa = $pessoal->get_idPessoa($idServidor);
    
        # Pega quantos dias foram publicados
        $select = "SELECT SUM(numDias) 
                     FROM tbpublicacaopremio LEFT JOIN tbservidor USING (idServidor)
                                             LEFT JOIN tbpessoa USING (idPessoa)
                    WHERE idPessoa = $idPessoa
                      AND tbservidor.idPerfil = 1";

        # Pega os valores
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

    function get_numDiasDisponiveisTotal($idServidor){

    /**
     * Informe o número de dias disponíveis
     */

        $diasPublicados = $this->get_NumDiasPublicadosTotal($idServidor);
        $diasFruidos = $this->get_NumDiasFruidosTotal($idServidor);
        $diasDisponiveis = $diasPublicados - $diasFruidos;
        
        # Retorno
        return $diasDisponiveis;
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
        
        if(is_numeric($idServidor)){
        
            # Pega os dias publicados
            $select = 'SELECT processoPremio
                         FROM tbservidor
                        WHERE idServidor = '.$idServidor;

            $retorno = $pessoal->select($select,FALSE);

            # Retorno
            return $retorno[0];
        }else{
            return $idServidor;
        }
        
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

    function get_numPublicacoesTotal($idServidor){

    /**
     * Informe o número de publicações de Licença Prêmio de todos os vinculos de um servidor
     */

         # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega o idPessoa
        $idPessoa = $pessoal->get_idPessoa($idServidor);
    
        # Pega quantos dias foram publicados
        $select = "SELECT idPublicacaoPremio
                     FROM tbpublicacaopremio LEFT JOIN tbservidor USING (idServidor)
                                             LEFT JOIN tbpessoa USING (idPessoa)
                    WHERE idPessoa = $idPessoa
                      AND tbservidor.idPerfil = 1";

        # Pega os valores
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
        
        # Pega os dados do servidor
        $idSituacao = $pessoal->get_idSituacao($idServidor);
        
        # Se for inativo o calculo é feito na data de saída
        if($idSituacao <> 1){
            $ds = $pessoal->get_dtSaida($idServidor);
        }else{
            # Pega a ano atual
            $ds = date("d/m/Y");
        }
                
        $data1 = new DateTime(date_to_bd($da));
        $data2 = new DateTime(date_to_bd($ds));

        $intervalo = $data1->diff( $data2 );
       
        $pp = $intervalo->y;
        return intval($pp/5);
    }

    ########################################################### 
 

    function get_numPublicacoesPossiveisTotal($idServidor){

    /**
     * Informe o número de publicações Possíveis de Licença Prêmio de um servidor, O número que ele deveria ter desde a data de admissão.
     */

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        
        # Pega os Dados 
        $numVinculos = $this->get_numVinculosPremio($idServidor);
            
        # Carrega um array com os idServidor de cada vinculo
        $vinculos = $this->get_vinculosPremio($idServidor);     
        
        $contador = 0;
        $ds = date("d/m/Y");

        # Percorre os vinculos
        foreach($vinculos as $tt){
            
            # Verifica se é o primeiro vínculo e pega a data de admissão
            if($contador == 0){
                $da = $pessoal->get_dtAdmissao($tt[0]);
            }
            
            # Verifica se é o último vínculo e pega a data de saída
            if($contador == ($numVinculos - 1)){
                
                # Pega a situação desse vinculo
                $idSituacao = $pessoal->get_idSituacao($tt[0]);
                
                # Verifica se está ativo
                if($idSituacao <> 1){
                    $ds = $pessoal->get_dtSaida($tt[0]);
                }else{
                    $ds = date("d/m/Y");
                }
            }
            $contador++;
        }            
        #echo $da." - ".$ds;
        $data1 = new DateTime(date_to_bd($da));
        $data2 = new DateTime(date_to_bd($ds));

        $intervalo = $data1->diff( $data2 );
       
        $pp = $intervalo->y;
        return intval($pp/5);
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

    function get_numPublicacoesFaltantesTotal($idServidor){

    /**
     * Informe o número de publicações Que faltam ser publicadas.
     */

        # Pega publicações feitas 
        $pf = $this->get_numPublicacoesTotal($idServidor);
        
        # Pega o número de Publicações Possíveis
        $pp = $this->get_numPublicacoesPossiveisTotal($idServidor);
                
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
        $numVinculos = $this->get_numVinculosPremio($idServidor);
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

        # Verifica os vinculos anteriores
        if($numVinculos > 0){
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
                        $cargo[] = "Vínculo<br>".$pessoal->get_cargoSimples($tt[0]);

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
        $cargo[] = "Vínculo<br>".$pessoal->get_cargoSimples($idServidor);

        # Totais
        $diasPublicadosTotal += $this->get_numDiasPublicados($idServidor);
        $diasFruidosTotal += $this->get_numDiasFruidos($idServidor);
        $diasDisponiveisTotal += $this->get_numDiasDisponiveis($idServidor);

        if($numVinculos > 0){
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
                
        if($diasPublicadosTotal > 0){
            # Conecta com o banco de dados
            $pessoal = new Pessoal();

            # Exibe as Publicações
            $select = 'SELECT idServidor, 
                              dtPublicacao,
                              dtInicioPeriodo,
                              dtFimPeriodo,
                              numDias,
                              idPublicacaoPremio,
                              idPublicacaoPremio,
                              idPublicacaoPremio
                         FROM tbpublicacaopremio
                        WHERE idServidor = '.$idServidor;

            # Inclui as publicações de outros vinculos
            if($numVinculos > 0){
                # Percorre os vinculos
                foreach($vinculos as $tt){
                    $select .= ' OR idServidor = '.$tt[0];
                }            
            }

            $select .= ' ORDER BY idServidor, dtInicioPeriodo desc';

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Cabeçalho da tabela
            $titulo = 'Publicações';
            $label = array("Vínculo","Data da Publicação","Período Aquisitivo <br/> Início","Período Aquisitivo <br/> Fim","Dias <br/> Publicados","Dias <br/> Fruídos","Dias <br/> Disponíveis");
            #$width = array(15,10,15,15,15,10,10,10);
            $funcao = array(NULL,'date_to_php','date_to_php','date_to_php');
            $classe = array("Pessoal",NULL,NULL,NULL,NULL,'LicencaPremio','LicencaPremio');
            $metodo = array("get_cargoSimples",NULL,NULL,NULL,NULL,'get_numDiasFruidosPorPublicacao','get_numDiasDisponiveisPorPublicacao');
            $align = array(NULL,'center');            

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

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_numeroOrdem(TRUE);
            $tabela->set_numeroOrdemTipo("d");


            $tabela->set_formatacaoCondicional(array(array('coluna' => 6,
                                                           'valor' => 0,
                                                           'operador' => '<',
                                                           'id' => 'alerta')));

            $tabela->show();
        }else{
            br();
           tituloTable("Publicações");
           $callout = new Callout();
           $callout->abre();
               p('Nenhum item encontrado !!','center');
           $callout->fecha();
       }
        
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
        $cargo = $pessoal->get_cargoSimples($idServidor);
        $idSituacao = $pessoal->get_idSituacao($idServidor);
        
        if($idSituacao == 1){
            $motivo = "Ativo";
        }
        
        # TítuloLink
        $titulo = "<a id='licencaPremio' href='?fase=outroVinculo&id=$idServidor'><b>Vínculo Anterior: Cargo $cargo:<br/>Admissão: $dtAdm - Saída: $dtSai ($motivo)</b></a>";
        
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

    ##########################################################################################

    public function get_numVinculosPremio($idServidor){

        # Função que retorna quantos vinculos esse servidor com direito a licença premio (estatutário)
        #
        # Parâmetro: id do servidor
        
            # Conecta com o banco de dados
            $pessoal = new Pessoal();
        
            # Valida parametro
            if(is_null($idServidor)){
                return FALSE;
            }            

            # Pega o idPessoa desse idServidor
            $idPessoa = $pessoal->get_idPessoa($idServidor);

            # Monta o select		
            $select = "SELECT idServidor
                         FROM tbservidor
                        WHERE idPessoa = $idPessoa
                          AND idPerfil = 1";  

            $numero = $pessoal->count($select);
            return $numero;
        }

    ##########################################################################################

    public function get_vinculosPremio($idServidor){

        # Função que retorna um array com o idServidor de cada vinculo
        #
        # Parâmetro: id do servidor
        
            # Conecta com o banco de dados
            $pessoal = new Pessoal();
        
            # Valida parametro
            if(is_null($idServidor)){
                return FALSE;
            }            

            # Pega o idPessoa desse idServidor
            $idPessoa = $pessoal->get_idPessoa($idServidor);

            # Monta o select		
            $select = "SELECT idServidor
                         FROM tbservidor
                        WHERE idPessoa = $idPessoa
                          AND idPerfil = 1
                     ORDER BY dtAdmissao";  

            $row = $pessoal->select($select);
            return $row;
        }

    ##########################################################################################

}