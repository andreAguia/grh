<?php

class LicencaPremio {

    /**
     * Exibe as informações sobre a licençca prêmio
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     */
    ###########################################################

    public function __construct() {

        /**
         * Inicia a classe 
         */
    }

    ###########################################################    

    function get_numDiasFruidos($idServidor) {

        /**
         * Informa a quantidade de dias fruídos
         */
        # Pega quantos dias foram fruídos
        $select = 'SELECT SUM(numDias) 
                     FROM tblicencapremio 
                    WHERE idServidor = ' . $idServidor;

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        if (is_null($row[0])) {
            return 0;
        } else {
            return $row[0];
        }
    }

    ########################################################### 

    function get_numDiasPublicados($idServidor) {

        /**
         * Informe o número de dias publicados
         */
        # Pega quantos dias foram publicados
        $select = 'SELECT SUM(numDias) 
                     FROM tbpublicacaopremio 
                    WHERE idServidor = ' . $idServidor;

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        if (is_null($row[0]))
            return 0;
        else
            return $row[0];
    }

    ###########################################################    

    function get_numDiasFruidosTotal($idServidor) {

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
        $row = $pessoal->select($select, false);

        # Retorno
        if (is_null($row[0]))
            return 0;
        else
            return $row[0];
    }

    ########################################################### 

    function get_numDiasPublicadosTotal($idServidor) {

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
        $row = $pessoal->select($select, false);

        # Retorno
        if (is_null($row[0]))
            return 0;
        else
            return $row[0];
    }

    ###########################################################

    function get_numDiasDisponiveis($idServidor) {

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

    function get_numDiasDisponiveisTotal($idServidor) {

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

    function get_publicacao($idLicencaPremio) {

        /**
         * Informe a publicação de uma licença
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = 'SELECT idPublicacaoPremio
                     FROM tblicencapremio
                    WHERE idLicencaPremio = ' . $idLicencaPremio;

        $retorno = $pessoal->select($select, false);

        return $retorno[0];
    }

    ###########################################################

    function get_numDiasFruidosPorPublicacao($idPublicacaoPremio) {

        /**
         * Informe o número de dias fruídos em uma Publicação
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        #  Pega quantos dias foram fruídos
        $select = 'SELECT SUM(numDias) 
                     FROM tblicencapremio 
                    WHERE idPublicacaoPremio = ' . $idPublicacaoPremio;

        $fruidos = $pessoal->select($select, false);

        # Retorna
        return $fruidos[0];
    }

    ###########################################################

    function get_numDiasDisponiveisPorPublicacao($idPublicacaoPremio) {

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

    function get_numDiasPublicadosPorPublicacao($idPublicacaoPremio) {

        /**
         * Informe o número de dias publicados
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT numDias
                     FROM tbpublicacaopremio 
                    WHERE idPublicacaoPremio = ' . $idPublicacaoPremio;

        $retorno = $pessoal->select($select, false);

        # Retorno
        return $retorno[0];
    }

    ###########################################################

    function get_numProcesso($idServidor) {

        /**
         * Informe o número do processo da licença prêmio de um servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        if (is_numeric($idServidor)) {

            # Pega os dias publicados
            $select = 'SELECT processoPremio
                         FROM tbservidor
                        WHERE idServidor = ' . $idServidor;

            $retorno = $pessoal->select($select, false);

            # Retorno
            return $retorno[0];
        } else {
            return $idServidor;
        }
    }

    ###########################################################

    function get_numPublicacoes($idServidor) {

        /**
         * Informe o número de publicações de Licença Prêmio de um servidor
         */
        # Pega quantos dias foram publicados
        $select = 'SELECT idPublicacaoPremio
                     FROM tbpublicacaopremio 
                    WHERE idServidor = ' . $idServidor;

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();
        $row = $pessoal->count($select);
        return $row;
    }

    ########################################################### 

    function get_numPublicacoesTotal($idServidor) {

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

    function get_numPublicacoesPossiveis($idServidor) {

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
        if ($idSituacao <> 1) {
            $ds = $pessoal->get_dtSaida($idServidor);
        } else {
            # Pega a ano atual
            $ds = date("d/m/Y");
        }

        $data1 = new DateTime(date_to_bd($da));
        $data2 = new DateTime(date_to_bd($ds));

        $intervalo = $data1->diff($data2);

        $pp = $intervalo->y;
        return intval($pp / 5);
    }

    ########################################################### 

    function get_numPublicacoesPossiveisTotal($idServidor) {

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
        $da = $pessoal->get_dtAdmissao($idServidor);

        # Percorre os vinculos
        foreach ($vinculos as $tt) {

            # Verifica se é o primeiro vínculo e pega a data de admissão
            if ($contador == 0) {
                $da = $pessoal->get_dtAdmissao($tt[0]);
            }

            # Verifica se é o último vínculo e pega a data de saída
            if ($contador == ($numVinculos - 1)) {

                # Pega a situação desse vinculo
                $idSituacao = $pessoal->get_idSituacao($tt[0]);

                # Verifica se está ativo
                if ($idSituacao <> 1) {
                    $ds = $pessoal->get_dtSaida($tt[0]);
                } else {
                    $ds = date("d/m/Y");
                }
            }
            $contador++;
        }
        #echo $da." - ".$ds;
        $data1 = new DateTime(date_to_bd($da));
        $data2 = new DateTime(date_to_bd($ds));

        $intervalo = $data1->diff($data2);

        $pp = $intervalo->y;
        return intval($pp / 5);
    }

    ###########################################################  

    function get_numPublicacoesFaltantes($idServidor) {

        /**
         * Informe o número de publicações Que faltam ser publicadas.
         */
        # Pega o número de Publicações Possíveis
        $pp = $this->get_numPublicacoesPossiveis($idServidor);

        # Pega publicações feitas 
        $pf = $this->get_numPublicacoes($idServidor);

        # Calcula o número de publicações faltantes
        $pfalt = $pp - $pf;

        # Retorna o valor
        return $pfalt;
    }

    ###########################################################  

    function get_numPublicacoesFaltantesTotal($idServidor) {

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

    public function exibePublicacoesPremio($idServidor) {

        /**
         * Exibe uma tabela com as publicações de Licença Prêmio de um servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega o número de vínculos
        $numVinculos = $this->get_numVinculosPremio($idServidor);

        /*
         * Exibe o número do processo
         */

        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(4);

        if ($numVinculos > 1) {
            # Carrega um array com os idServidor de cada vinculo
            $vinculos = $this->get_vinculosPremio($idServidor, false);

            # Percorre os vinculos
            foreach ($vinculos as $tt) {
                # Insere no array o vinculo e o processo
                $conteudo1[] = [
                    $pessoal->get_cargoSigla($tt[0]),
                    $this->get_numProcesso($tt[0])
                ];
            }

            $tabela = new Tabela();
            $tabela->set_conteudo($conteudo1);
            $tabela->set_align(["left"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_titulo("Processo");
            $tabela->set_width([50, 50]);
            $tabela->set_label(["Vínculo", "Processos"]);
            $tabela->set_grupoCorColuna(0);
            $tabela->show();
        } else {
            titulotable("Processo");
            $painel = new Callout();
            $painel->abre();
            p($this->get_numProcesso($idServidor), "f20", "center");
            $painel->fecha();
        }

        /*
         * Exibe o número de publicação
         */

        # Verifica qual rotina vai executar
        if ($numVinculos > 1) {
            # Carrega um array com os idServidor de cada vinculo
            $vinculos = $pessoal->get_vinculos($idServidor, false);

            # Percorre os vinculos
            foreach ($vinculos as $tt) {
                # Insere no array o vinculo e o processo
                $conteudo2[] = [
                    $pessoal->get_cargoSigla($tt[0]),
                    $this->get_numPublicacoesPossiveis($tt[0]),
                    $this->get_numPublicacoes($tt[0]),
                    $this->get_numPublicacoesFaltantes($tt[0])
                ];
            }

            $tabela = new Tabela();
            $tabela->set_titulo("N° de Publicações");
            $tabela->set_align(["left"]);
            $tabela->set_conteudo($conteudo2);
            $tabela->set_grupoCorColuna(0);
            $tabela->set_label(["Vínculo", "Possíveis", "Publicadas", "Pendentes"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_colunaSomatorio([1, 2, 3]);
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 3,
                    'valor' => 0,
                    'operador' => '<',
                    'id' => 'alerta')));
            $tabela->show();
        } else {

            $conteudo[] = [
                $this->get_numPublicacoesPossiveis($idServidor),
                $this->get_numPublicacoes($idServidor),
                $this->get_numPublicacoesFaltantes($idServidor)
            ];

            $tabela = new Tabela();
            $tabela->set_titulo("N° de Publicações");
            $tabela->set_conteudo($conteudo);
            $tabela->set_label(["Possíveis", "Publicadas", "Pendentes"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 2,
                    'valor' => 0,
                    'operador' => '<>',
                    'id' => 'alerta')));
            $tabela->show();
        }

        /*
         * Exibe a lista de publicações
         */

        $grid->fechaColuna();
        $grid->abreColuna(8);
        
        # Exibe as notificações
        $this->exibeOcorrencias($idServidor);

        if ($numVinculos > 1) {

            # Exibe as Publicações
            $select = 'SELECT idServidor, 
                              dtPublicacao,
                              idPublicacaoPremio,
                              numDias,
                              idPublicacaoPremio,
                              idPublicacaoPremio,
                              idPublicacaoPremio,
                              idPublicacaoPremio
                         FROM tbpublicacaopremio
                        WHERE idServidor = ' . $idServidor;

            # Inclui as publicações de outros vinculos
            if ($numVinculos > 1) {
                # Percorre os vinculos
                foreach ($vinculos as $tt) {
                    if ($tt[0] <> $idServidor) {
                        $select .= ' OR idServidor = ' . $tt[0];
                    }
                }
            }

            $select .= ' ORDER BY dtInicioPeriodo desc';

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_titulo('Publicações');
            $tabela->set_label(["Vínculos", "Data da Publicação", "Período Aquisitivo ", "Dias <br/> Publicados", "Dias <br/> Fruídos", "Dias <br/> Disponíveis", "Obs"]);
            $tabela->set_width([23, 12, 23, 10, 10, 10, 12]);
            $tabela->set_align(["left"]);
            $tabela->set_funcao([null, 'date_to_php']);
            $tabela->set_classe(["Pessoal", null, 'LicencaPremio', null, 'LicencaPremio', 'LicencaPremio', 'LicencaPremio']);
            $tabela->set_metodo(["get_cargoSimples", null, "exibePeriodoAquisitivo2", null, 'get_numDiasFruidosPorPublicacao', 'get_numDiasDisponiveisPorPublicacao', 'exibeObsPublicacao']);

            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_colunaSomatorio([3, 4, 5]);
            $tabela->set_totalRegistro(false);

            $tabela->set_numeroOrdem(true);
            $tabela->set_numeroOrdemTipo("d");
            $tabela->show();
        } else {
            # Exibe as Publicações
            $select = "SELECT dtPublicacao,
                              CONCAT(date_format(dtInicioPeriodo,'%d/%m/%Y'),' - ',date_format(dtFimPeriodo,'%d/%m/%Y')),
                              numDias,
                              idPublicacaoPremio,
                              idPublicacaoPremio,
                              idPublicacaoPremio,
                              idPublicacaoPremio
                         FROM tbpublicacaopremio
                        WHERE idServidor = {$idServidor}
                        ORDER BY dtInicioPeriodo desc";

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_titulo('Publicações');
            $tabela->set_label(["Data da Publicação", "Período Aquisitivo ", "Dias <br/> Publicados", "Dias <br/> Fruídos", "Dias <br/> Disponíveis", "Obs"]);
            $tabela->set_width([14, 30, 14, 14, 14, 14]);
            $tabela->set_funcao(['date_to_php']);
            $tabela->set_classe([null, null, null, 'LicencaPremio', 'LicencaPremio', 'LicencaPremio']);
            $tabela->set_metodo([null, null, null, 'get_numDiasFruidosPorPublicacao', 'get_numDiasDisponiveisPorPublicacao', 'exibeObsPublicacao']);

            $tabela->set_numeroOrdem(true);
            $tabela->set_numeroOrdemTipo("d");

            $tabela->set_colunaSomatorio([2, 3, 4]);
            #$tabela->set_colunaSomatorio(2);
            $tabela->set_totalRegistro(false);
            $tabela->show();
        }

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

###########################################################

    public function exibePublicacoesPremioRelatório($idServidor) {

        /**
         * Exibe um relatório com as publicações de Licença Prêmio de um servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega o número de vínculos
        $numVinculos = $this->get_numVinculosPremio($idServidor);

        /*
         * Exibe o número do processo
         */

        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(4);

        if ($numVinculos > 1) {
            # Carrega um array com os idServidor de cada vinculo
            $vinculos = $this->get_vinculosPremio($idServidor, false);

            # Percorre os vinculos
            foreach ($vinculos as $tt) {
                # Insere no array o vinculo e o processo
                $conteudo1[] = [
                    $pessoal->get_cargoSigla($tt[0]),
                    $this->get_numProcesso($tt[0])
                ];
            }

            tituloRelatorio('Processo');

            $relatorio = new Relatorio();
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_subTotal(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_width([40, 60]);
            $relatorio->set_label(["Vínculo", "Processos"]);
            $relatorio->set_align(["left", "left"]);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->set_conteudo($conteudo1);
            $relatorio->set_botaoVoltar(false);
            $relatorio->set_log(false);
            $relatorio->show();
        } else {
            tituloRelatorio('Processo');
            p($this->get_numProcesso($idServidor), "pFichaCadastralProcessoPremio");
            hr("nenhumItem");
        }

        /*
         * Exibe o número de publicação
         */

        # Verifica qual rotina vai executar
        if ($numVinculos > 1) {
            # Carrega um array com os idServidor de cada vinculo
            $vinculos = $pessoal->get_vinculos($idServidor, false);

            # Percorre os vinculos
            foreach ($vinculos as $tt) {
                # Insere no array o vinculo e o processo
                $conteudo2[] = [
                    $pessoal->get_cargoSigla($tt[0]),
                    $this->get_numPublicacoesPossiveis($tt[0]),
                    $this->get_numPublicacoes($tt[0]),
                    $this->get_numPublicacoesFaltantes($tt[0])
                ];
            }

            tituloRelatorio('"N° de Publicações"');

            $relatorio = new Relatorio();
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_subTotal(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_label(["Vínculo", "Possíveis", "Publicadas", "Pendentes"]);
            $relatorio->set_colunaSomatorio([1, 2, 3]);
            $relatorio->set_align(["left"]);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->set_conteudo($conteudo2);
            $relatorio->set_botaoVoltar(false);
            $relatorio->set_log(false);
            $relatorio->show();
        } else {

            $conteudo[] = [
                $this->get_numPublicacoesPossiveis($idServidor),
                $this->get_numPublicacoes($idServidor),
                $this->get_numPublicacoesFaltantes($idServidor)
            ];

            tituloRelatorio("N° de Publicações");

            $relatorio = new Relatorio();
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_subTotal(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_label(["Possíveis", "Publicadas", "Pendentes"]);
            $relatorio->set_align(["center"]);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->set_conteudo($conteudo);
            $relatorio->set_botaoVoltar(false);
            $relatorio->set_log(false);
            $relatorio->show();
        }

        /*
         * Exibe a lista de publicações
         */

        $grid->fechaColuna();
        $grid->abreColuna(8);

        if ($numVinculos > 1) {

            # Exibe as Publicações
            $select = 'SELECT idServidor, 
                              dtPublicacao,
                              idPublicacaoPremio,
                              numDias,
                              idPublicacaoPremio,
                              idPublicacaoPremio,
                              idPublicacaoPremio
                         FROM tbpublicacaopremio
                        WHERE idServidor = ' . $idServidor;

            # Inclui as publicações de outros vinculos
            if ($numVinculos > 1) {
                # Percorre os vinculos
                foreach ($vinculos as $tt) {
                    if ($tt[0] <> $idServidor) {
                        $select .= ' OR idServidor = ' . $tt[0];
                    }
                }
            }

            $select .= ' ORDER BY dtInicioPeriodo desc';
            $result = $pessoal->select($select);

            tituloRelatorio("Publicações");

            $relatorio = new Relatorio();
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_subTotal(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_label(["Vínculos", "Data da Publicação", "Período Aquisitivo ", "Dias <br/> Publicados", "Dias <br/> Fruídos", "Dias <br/> Disponíveis"]);
            $relatorio->set_align(["left"]);
            $relatorio->set_funcao([null, 'date_to_php']);
            #$relatorio->set_width([23, 12, 23, 10, 10, 10, 12]);
            $relatorio->set_classe(["Pessoal", null, 'LicencaPremio', null, 'LicencaPremio', 'LicencaPremio', 'LicencaPremio']);
            $relatorio->set_metodo(["get_cargoSigla", null, "exibePeriodoAquisitivo2", null, 'get_numDiasFruidosPorPublicacao', 'get_numDiasDisponiveisPorPublicacao']);
            $relatorio->set_colunaSomatorio([3, 4, 5]);
            $relatorio->set_numeroOrdem(true);
            $relatorio->set_numeroOrdemTipo("d");
            $relatorio->set_totalRegistro(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->set_conteudo($result);
            $relatorio->set_botaoVoltar(false);
            $relatorio->set_log(false);
            $relatorio->show();
        } else {
            # Exibe as Publicações
            $select = "SELECT dtPublicacao,
                              idPublicacaoPremio,
                              numDias,
                              idPublicacaoPremio,
                              idPublicacaoPremio,
                              idPublicacaoPremio
                         FROM tbpublicacaopremio
                        WHERE idServidor = {$idServidor}
                        ORDER BY dtInicioPeriodo desc";

            $result = $pessoal->select($select);

            tituloRelatorio("Publicações");

            $relatorio = new Relatorio();
            $relatorio->set_cabecalhoRelatorio(false);
            $relatorio->set_menuRelatorio(false);
            $relatorio->set_subTotal(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_label(["Data da Publicação", "Período Aquisitivo ", "Dias <br/> Publicados", "Dias <br/> Fruídos", "Dias <br/> Disponíveis"]);
            $relatorio->set_align(["left"]);
            $relatorio->set_funcao(['date_to_php']);
            #$relatorio->set_width([23, 12, 23, 10, 10, 10, 12]);
            $relatorio->set_classe([null, 'LicencaPremio', null, 'LicencaPremio', 'LicencaPremio', 'LicencaPremio']);
            $relatorio->set_metodo([null, "exibePeriodoAquisitivo2", null, 'get_numDiasFruidosPorPublicacao', 'get_numDiasDisponiveisPorPublicacao']);
            $relatorio->set_colunaSomatorio([2, 3, 4]);
            $relatorio->set_numeroOrdem(true);
            $relatorio->set_numeroOrdemTipo("d");
            $relatorio->set_totalRegistro(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->set_conteudo($result);
            $relatorio->set_botaoVoltar(false);
            $relatorio->set_log(false);
            $relatorio->show();
        }

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

###########################################################

    public function exibeProcedimentos() {

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

    public function exibeLicencaPremio($idServidor) {

        /**
         * Exibe uma tabela com as Licença Prêmio de um servidor
         */
        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        # Exibe as Publicações
        $select = 'SELECT tbpublicacaopremio.dtPublicacao,
                          CONCAT(DATE_FORMAT(dtInicioPeriodo, "%d/%m/%Y")," - ",DATE_FORMAT(dtFimPeriodo, "%d/%m/%Y")),
                          dtInicial,
                          tblicencapremio.numdias,
                          ADDDATE(dtInicial,tblicencapremio.numDias-1),
                          idLicencaPremio,
                          idLicencaPremio
                     FROM tblicencapremio LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                    WHERE tblicencapremio.idServidor = ' . $idServidor . '
                 ORDER BY dtInicial desc';

        $result = $pessoal->select($select);

        # Dados do vínculo
        $dtAdm = $pessoal->get_dtAdmissao($idServidor);
        $dtSai = $pessoal->get_dtSaida($idServidor);
        $motivo = $pessoal->get_motivo($idServidor);
        $cargo = $pessoal->get_cargoSimples($idServidor);
        $idSituacao = $pessoal->get_idSituacao($idServidor);

        if ($idSituacao == 1) {
            $motivo = "Ativo";
        }

        # Cria um menu
        $menu = new MenuBar();

        # Cadastro de Publicações
        $linkBotao3 = new Link("Ir para o Vínculo abaixo", "?fase=outroVinculo&id={$idServidor}");
        $linkBotao3->set_class('button');
        $linkBotao3->set_title("Acessa o Cadastro de Publicações");
        $menu->add_link($linkBotao3, "right");
        $menu->show();

        # TítuloLink
        $titulo = "Licenças Fruídas do Vínculo: Cargo $cargo<br/>Admissão: $dtAdm - Saída: $dtSai ($motivo)";

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_titulo($titulo);
        $tabela->set_conteudo($result);
        $tabela->set_label(array("Data da Publicaçãod", "Período Aquisitivo", "Inicio", "Dias", "Término", "Obs"));
        $tabela->set_align(array("center"));
        $tabela->set_funcao(array('date_to_php', null, 'date_to_php', null, 'date_to_php'));
        $tabela->set_width(array(17, 22, 17, 10, 17, 12));
        $tabela->set_classe(array(null, null, null, null, null, 'LicencaPremio'));
        $tabela->set_metodo(array(null, null, null, null, null, 'exibeObs'));
        $tabela->set_numeroOrdem(true);
        $tabela->set_numeroOrdemTipo("d");
        $tabela->set_exibeTempoPesquisa(false);
        $tabela->show();
    }

    ###########################################################

    public function exibeLicencaPremioRelatorio($idServidor) {

        /**
         * Exibe umrelatório tabela com as Licença Prêmio de um servidor
         * Usado nos relatório de licenla premio e na ficha cadastral
         */
        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        # Exibe as Publicações
        $select = 'SELECT tbpublicacaopremio.dtPublicacao,
                          CONCAT(DATE_FORMAT(dtInicioPeriodo, "%d/%m/%Y")," - ",DATE_FORMAT(dtFimPeriodo, "%d/%m/%Y")),
                          dtInicial,
                          tblicencapremio.numdias,
                          ADDDATE(dtInicial,tblicencapremio.numDias-1),
                          idLicencaPremio
                     FROM tblicencapremio LEFT JOIN tbpublicacaopremio USING (idPublicacaoPremio)
                    WHERE tblicencapremio.idServidor = ' . $idServidor . '
                 ORDER BY dtInicial desc';

        $result = $pessoal->select($select);
        $count = $pessoal->count($select);

        # Dados do vínculo
        $dtAdm = $pessoal->get_dtAdmissao($idServidor);
        $dtSai = $pessoal->get_dtSaida($idServidor);
        $motivo = $pessoal->get_motivo($idServidor);
        $cargo = $pessoal->get_cargoSimples($idServidor);
        $idSituacao = $pessoal->get_idSituacao($idServidor);

        if ($idSituacao == 1) {
            tituloRelatorio("Licenças Fruídas do Vínculo: Cargo {$cargo} / Admissão: {$dtAdm} - (Ativo)");
        } else {
            tituloRelatorio("Licenças Fruídas do Vínculo: Cargo {$cargo} / Admissão: {$dtAdm} - Saída: {$dtSai} ({$motivo})");
        }

        $relatorio = new Relatorio();
        $relatorio->set_cabecalhoRelatorio(false);
        $relatorio->set_menuRelatorio(false);
        $relatorio->set_subTotal(true);
        $relatorio->set_totalRegistro(false);
        $relatorio->set_dataImpressao(false);
        $relatorio->set_numeroOrdem(true);
        $relatorio->set_numeroOrdemTipo("d");
        #$relatorio->set_subtitulo("Licenças Fruídas");
        $relatorio->set_label(array("Publicação", "Período Aquisitivo", "Inicio", "Dias", "Término"));
        #$relatorio->set_width(array(23,10,5,10,17,10,10,10,5));
        $relatorio->set_align(array('center'));
        $relatorio->set_funcao(array('date_to_php', null, 'date_to_php', null, 'date_to_php'));

        $relatorio->set_conteudo($result);
        #$relatorio->set_numGrupo(2);
        $relatorio->set_botaoVoltar(false);
        $relatorio->show();
    }

    ##########################################################################################

    public function get_numVinculosPremio($idServidor) {

        # Função que retorna quantos vinculos esse servidor com direito a licença premio (estatutário)
        #
        # Parâmetro: id do servidor
        # Conecta com o banco de dados
        $pessoal = new Pessoal();

        # Valida parametro
        if (is_null($idServidor)) {
            return false;
        }

        # Pega o idPessoa desse idServidor
        $idPessoa = $pessoal->get_idPessoa($idServidor);

        # Monta o select		
        $select = "SELECT idServidor
                         FROM tbservidor
                        WHERE idPessoa = {$idPessoa}
                          AND idPerfil = 1";

        $numero = $pessoal->count($select);
        return $numero;
    }

    ##########################################################################################

    function get_vinculosPremio($idServidor, $crescente = true) {

        # Função que retorna o idServidor de cada vinculos esse servidor teve com a uenf com direito a lic premio.
        #
        # Parâmetro: id do servidor

        $pessoal = new Pessoal();

        # Valida parametro
        if (is_null($idServidor)) {
            return false;
        }

        # Pega o idPessoa desse idServidor
        $idPessoa = $pessoal->get_idPessoa($idServidor);

        # Monta o select
        if ($crescente) {
            $select = "SELECT idServidor
                         FROM tbservidor
                        WHERE idPessoa = {$idPessoa}
                          AND idPerfil = 1
                     ORDER BY dtadmissao";
        } else {
            $select = "SELECT idServidor
                         FROM tbservidor
                        WHERE idPessoa = {$idPessoa}
                          AND idPerfil = 1
                     ORDER BY dtadmissao desc";
        }

        $row = $pessoal->select($select);
        return $row;
    }

    ##########################################################################################

    public function exibeObs($idLicencaPremio) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = 'SELECT obs
                     FROM tblicencapremio
                    WHERE idLicencaPremio = ' . $idLicencaPremio;

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno[0]);
        }
    }

###########################################################

    public function exibeObsPublicacao($idPublicacaoPremio) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = 'SELECT obs
                     FROM tbpublicacaopremio
                    WHERE idPublicacaoPremio = ' . $idPublicacaoPremio;

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno[0]);
        }
    }

###########################################################                                        

    function exibePeriodoAquisitivo($idLicencaPremio) {

        /**
         * Informa o período Aquisitivo
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = 'SELECT dtInicioPeriodo,
                          dtFimPeriodo
                     FROM tbpublicacaopremio LEFT JOIN tblicencapremio USING (idPublicacaoPremio)
                    WHERE idLicencaPremio = ' . $idLicencaPremio;

        $row = $pessoal->select($select, false);

        return date_to_php($row['dtInicioPeriodo']) . " - " . date_to_php($row['dtFimPeriodo']);
    }

    ###########################################################                          

    function exibePeriodoAquisitivo2($idPublicacaoPremio) {

        /**
         * Informa o período Aquisitivo
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = 'SELECT dtInicioPeriodo,
                          dtFimPeriodo
                     FROM tbpublicacaopremio
                    WHERE idPublicacaoPremio = ' . $idPublicacaoPremio;

        $row = $pessoal->select($select, false);

        return strval(date_to_php($row['dtInicioPeriodo']) . " - " . date_to_php($row['dtFimPeriodo']));
    }

    ###########################################################

    function get_obsGeral($idServidor) {

        /**
         * Informe obs da licença prêmio de um servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        if (is_numeric($idServidor)) {

            # Pega os dias publicados
            $select = 'SELECT obsPremio
                         FROM tbservidor
                        WHERE idServidor = ' . $idServidor;

            $retorno = $pessoal->select($select, false);

            # Retorno
            return $retorno[0];
        } else {
            return $idServidor;
        }
    }

    ###########################################################

    function exibeOcorrencias($idServidor) {

        /**
         * Informe obs da licença prêmio de um servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dados 
        $diasPublicados = $this->get_numDiasPublicadosTotal($idServidor);
        $diasFruidos = $this->get_numDiasFruidosTotal($idServidor);
        $diasDisponiveis = $this->get_numDiasDisponiveisTotal($idServidor);
        $numProcesso = $this->get_numProcesso($idServidor);

        $nome = $pessoal->get_licencaNome(6);
        $idSituacao = $pessoal->get_idSituacao($idServidor);

        # inicia o array das rotinas extras
        $rotinaExtra = array();
        $rotinaExtraParametro = array();
        $mensagem = null;

        # Exibe alerta se $diasDisponíveis for negativo no geral
        if ($diasDisponiveis < 0) {
            $mensagem .= "Servidor tem mais dias fruídos de $nome do que publicados.<br/>";
        }

        # Servidor sem dias disponíveis. Precisa publicar antes de tirar nova licença
        if ($diasDisponiveis < 1) {
            $mensagem .= "Servidor sem dias disponíveis. É necessário cadastrar uma publicação para incluir uma licença prêmio.<br/>";
        }

        # Servidor sem processo cadastrado
        if (is_null($numProcesso)) {
            $mensagem .= "Servidor sem número de processo de $nome cadastrado.<br/>";
        }

        # Servidor com publicação pendente
        if ($this->get_numPublicacoesFaltantes($idServidor) > 0) {
            $mensagem .= "Existem publicações pendentes para este servidor.<br/>";
        }

        if (!is_null($mensagem)) {
            $painel = new Callout("warning");
            $painel->abre();

            p("Ocorrências:", "labelOcorrencias");
            p($mensagem,"left","f14");

            $painel->fecha();
        }
    }

    ###########################################################

    function exibeObsGeral($idServidor) {
        
        # Pega os Dados
        $mensagem = $this->get_obsGeral($idServidor);

        if (!is_null($mensagem)) {
            $painel = new Callout("warning");
            $painel->abre();

            p("observação Geral:", "labelOcorrencias");
            p(nl2br($mensagem),"left","f14");

            $painel->fecha();
        }
    }

    ###########################################################
}
