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
                    WHERE idPessoa = {$idPessoa}
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
        $select = "SELECT idPublicacaoPremio
                     FROM tblicencapremio
                    WHERE idLicencaPremio = {$idLicencaPremio}";

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
        $select = "SELECT numDias
                     FROM tbpublicacaopremio 
                    WHERE idPublicacaoPremio = {$idPublicacaoPremio}";

        $retorno = $pessoal->select($select, false);

        # Retorno
        return $retorno[0];
    }

    ###########################################################

    function get_numProcessoContagem($idServidor) {

        /**
         * Informe o número do processo de Contagem da licença prêmio de um servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        if (is_numeric($idServidor)) {

            # Pega os dias publicados
            $select = "SELECT processoPremio
                         FROM tbservidor
                        WHERE idServidor = {$idServidor}";

            $retorno = $pessoal->select($select, false);

            # Retorno
            return $retorno[0];
        } else {
            return $idServidor;
        }
    }

    ###########################################################

    function get_numProcessoFruicao($id) {

        /**
         * Informe o número do processo de Fruicao da licença prêmio de um servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        if (is_numeric($id)) {

            # Pega os dias publicados
            $select = "SELECT processo
                         FROM tblicencapremio
                        WHERE idLicencaPremio = {$id}";

            $retorno = $pessoal->select($select, false);

            # Retorno
            return $retorno[0];
        } else {
            return $id;
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

    public function exibeProcessoContagem($idServidor = null) {

        # Valida $id
        if (empty($idServidor)) {
            return null;
        } else {
            # Conecta ao Banco de Dados
            $pessoal = new Pessoal();

            # Pega o número de vínculos
            $numVinculos = $this->get_numVinculosPremio($idServidor);

            if ($numVinculos > 1) {
                # Carrega um array com os idServidor de cada vinculo
                $vinculos = $this->get_vinculosPremio($idServidor, false);

                # Percorre os vinculos
                foreach ($vinculos as $tt) {
                    # Insere no array o vinculo e o processo
                    $conteudo1[] = [
                        $pessoal->get_cargoSigla($tt[0]),
                        $this->get_numProcessoContagem($tt[0])
                    ];
                }

                $tabela = new Tabela();
                $tabela->set_conteudo($conteudo1);
                $tabela->set_align(["left"]);
                $tabela->set_totalRegistro(false);
                $tabela->set_titulo("Processo de Contagem");
                $tabela->set_width([50, 50]);
                $tabela->set_label(["Vínculo", "Processos"]);
                $tabela->set_grupoCorColuna(0);
                $tabela->show();
            } else {
                titulotable("Processo de Contagem");
                $painel = new Callout();
                $painel->abre();
                p(trataNulo($this->get_numProcessoContagem($idServidor)), "f20", "center");
                $painel->fecha();
            }
        }
    }

    ###########################################################

    public function exibeNumeroPublicacoes($idServidor) {

        # Valida $id
        if (empty($idServidor)) {
            return null;
        } else {
            # Conecta ao Banco de Dados
            $pessoal = new Pessoal();

            # Pega o número de vínculos
            $numVinculos = $this->get_numVinculosPremio($idServidor);

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
        }
    }

    ###########################################################

    public function exibePublicacoes($idServidor = null, $reduzido = false) {

        /**
         * Exibe uma tabela com as publicações de Licença Prêmio de um servidor
         */
        # Pega o número de vínculos
        $numVinculos = $this->get_numVinculosPremio($idServidor);

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # exibe a tabela
        if ($numVinculos > 1) {

            # Carrega um array com os idServidor de cada vinculo
            $vinculos = $pessoal->get_vinculos($idServidor, false);

            # Exibe as Publicações
            $select = 'SELECT idServidor, 
                              dtPublicacao,
                              idPublicacaoPremio,
                              numDias,
                              idPublicacaoPremio,
                              idPublicacaoPremio,
                              idPublicacaoPremio,
                              idPublicacaoPremio,
                              idPublicacaoPremio
                         FROM tbpublicacaopremio JOIN tbservidor USING (idServidor)
                                                 JOIN tbcargo USING (idCargo)
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

            $select .= ' ORDER BY idTipoCargo, dtInicioPeriodo desc';

            $result = $pessoal->select($select);
            $count = $pessoal->count($select);

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_conteudo($result);
            $tabela->set_titulo('Publicações');
            $tabela->set_align(["left"]);

            if ($reduzido) {
                $tabela->set_label(["Vínculos", "Data da Publicação", "Período Aquisitivo ", "Dias <br/> Publicados", "Dias <br/> Fruídos", "Dias <br/> Disponíveis"]);
                #$tabela->set_width([23, 12, 23, 10, 10, 10, 12]);
                $tabela->set_classe(["Pessoal", null, 'LicencaPremio', null, 'LicencaPremio', 'LicencaPremio']);
                $tabela->set_metodo(["get_cargoSimples", null, "exibePeriodoAquisitivo2", null, 'get_numDiasFruidosPorPublicacao', 'get_numDiasDisponiveisPorPublicacao']);
            } else {
                $tabela->set_label(["Vínculos", "Data da Publicação", "Período Aquisitivo ", "Dias <br/> Publicados", "Dias <br/> Fruídos", "Dias <br/> Disponíveis", "DOERJ", "Obs"]);
                $tabela->set_width([23, 12, 23, 10, 10, 10, 12]);
                $tabela->set_classe(["Pessoal", null, 'LicencaPremio', null, 'LicencaPremio', 'LicencaPremio', 'LicencaPremio', 'LicencaPremio']);
                $tabela->set_metodo(["get_cargoSimples", null, "exibePeriodoAquisitivo2", null, 'get_numDiasFruidosPorPublicacao', 'get_numDiasDisponiveisPorPublicacao', 'exibeDoerj', 'exibeObsPublicacao']);
            }

            $tabela->set_funcao([null, 'date_to_php']);
            $tabela->set_rowspan(0);
            $tabela->set_grupoCorColuna(0);

            $tabela->set_colunaSomatorio([3, 4, 5]);
            $tabela->set_totalRegistro(false);

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
                    'valor' => '90',
                    'operador' => '>',
                    'id' => 'alerta')
            ));

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
            $tabela->set_funcao(['date_to_php']);
            $tabela->set_titulo('Publicações');

            if ($reduzido) {
                $tabela->set_label(["Data da Publicação", "Período Aquisitivo ", "Dias <br/> Publicados", "Dias <br/> Fruídos", "Dias <br/> Disponíveis"]);
                #$tabela->set_width([15, 25, 10, 10, 10, 10, 10, 10]);
                $tabela->set_classe([null, null, null, 'LicencaPremio', 'LicencaPremio']);
                $tabela->set_metodo([null, null, null, 'get_numDiasFruidosPorPublicacao', 'get_numDiasDisponiveisPorPublicacao']);
            } else {
                $tabela->set_subtitulo("Próxima Publicação: <b>{$this->get_dataFinalProximaPeriodo($idServidor)}</b>");
                $tabela->set_label(["Data da Publicação", "Período Aquisitivo ", "Dias <br/> Publicados", "Dias <br/> Fruídos", "Dias <br/> Disponíveis", "DO", "Obs"]);
                $tabela->set_width([15, 25, 10, 10, 10, 10, 10, 10]);
                $tabela->set_classe([null, null, null, 'LicencaPremio', 'LicencaPremio', 'LicencaPremio', 'LicencaPremio']);
                $tabela->set_metodo([null, null, null, 'get_numDiasFruidosPorPublicacao', 'get_numDiasDisponiveisPorPublicacao', 'exibeDoerj', 'exibeObsPublicacao']);
            }

            $tabela->set_numeroOrdem(true);
            $tabela->set_numeroOrdemTipo("d");

            $tabela->set_mensagemPosTabela("Obs. Antes de informar ao servidor sobre a licença prêmio,"
                    . " verifique se o mesmo possui algum afastamento"
                    . " específico que poderia alterar as datas da"
                    . " licença. O sistema, ainda, não verifica"
                    . " essa informação.");

            $tabela->set_colunaSomatorio([2, 3, 4]);

            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 3,
                    'valor' => '90',
                    'operador' => '>',
                    'id' => 'alerta')
            ));

            $tabela->set_totalRegistro(false);
            $tabela->show();
        }
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

        if ($numVinculos > 1) {

            # Carrega um array com os idServidor de cada vinculo
            $vinculos = $this->get_vinculosPremio($idServidor, false);

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
            $relatorio->set_align(["center"]);
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
            $relatorio->set_log(false);
            $relatorio->show();
        }
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

    public function exibeLicencaPremio($idServidor, $outroVinculo = true) {

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
                          processo,
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

        # Verifica se é uma tabela para exibir  licença de outro vínculo
        if ($outroVinculo) {

            # Cria um menu
            $menu = new MenuBar();

            # Cadastro de Publicações
            $linkBotao3 = new Link("Ir para o Vínculo abaixo", "?fase=outroVinculo&id={$idServidor}");
            $linkBotao3->set_class('button');
            $linkBotao3->set_title("Acessa o Cadastro de Publicações");
            $menu->add_link($linkBotao3, "right");
            $menu->show();

            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Licenças Fruídas do Vínculo: Cargo $cargo<br/>Admissão: $dtAdm - Saída: $dtSai ($motivo)");
            $tabela->set_conteudo($result);
            $tabela->set_label(["Data da Publicação", "Período Aquisitivo", "Inicio", "Dias", "Término", "Processo de Fruição", "Obs"]);
            $tabela->set_align(["center"]);
            $tabela->set_funcao(['date_to_php', null, 'date_to_php', null, 'date_to_php']);
            $tabela->set_classe([null, null, null, null, null, null, 'LicencaPremio']);
            $tabela->set_metodo([null, null, null, null, null, null, 'exibeObs']);
            $tabela->set_numeroOrdem(true);
            $tabela->set_numeroOrdemTipo("d");
            $tabela->show();
        } else {
            # Exibe a tabela
            $tabela = new Tabela();
            $tabela->set_titulo("Licenças Fruídas");
            $tabela->set_conteudo($result);
            $tabela->set_label(["Data da Publicação", "Período Aquisitivo", "Inicio", "Dias", "Término", "Processo de Fruição"]);
            $tabela->set_align(["center"]);
            $tabela->set_funcao(['date_to_php', null, 'date_to_php', null, 'date_to_php']);
            $tabela->set_numeroOrdem(true);
            $tabela->set_numeroOrdemTipo("d");
            $tabela->show();
        }
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
                          processo,
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
        $relatorio->set_label(["Publicação", "Período Aquisitivo", "Inicio", "Dias", "Término", "Processo"]);
        $relatorio->set_align(['center']);
        $relatorio->set_funcao(['date_to_php', null, 'date_to_php', null, 'date_to_php']);

        $relatorio->set_conteudo($result);
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

            # Pega os dados
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
        $numProcesso = $this->get_numProcessoContagem($idServidor);

        $nome = $pessoal->get_licencaNome(6);
        $idSituacao = $pessoal->get_idSituacao($idServidor);

        # inicia o array das rotinas extras
        $rotinaExtra = array();
        $rotinaExtraParametro = array();
        $mensagem = null;

        # Exibe alerta se $diasDisponíveis for negativo no geral
        if ($diasDisponiveis < 0) {
            $mensagem .= "- Servidor tem mais dias fruídos de $nome do que publicados.<br/>";
        }

        /*
         *  Servidor sem dias disponíveis. Precisa publicar antes de tirar nova licença
         */
        if ($diasDisponiveis < 1) {
            $mensagem .= "- Servidor sem dias disponíveis. É necessário cadastrar uma publicação para incluir uma licença prêmio.<br/>";
        }

        /*
         *  Servidor sem processo cadastrado
         */
        if (is_null($numProcesso)) {
            $mensagem .= "- Servidor sem número de processo de contagem cadastrado.<br/>";
        }

        /*
         * Servidor com publicação pendente
         */
        if ($this->get_numPublicacoesFaltantes($idServidor) > 0) {
            $mensagem .= "- Existem publicações pendentes para este servidor.<br/>";
        }

        /*
         * Servidor com mais de 90 dias fruídos em uma única publicação
         */
        # Exibe as Publicações
        $select1 = "SELECT idPublicacaoPremio
                         FROM tbpublicacaopremio
                        WHERE idServidor = {$idServidor}";

        $row1 = $pessoal->select($select1);

        foreach ($row1 as $item) {
            if ($this->get_numDiasFruidosPorPublicacao($item['idPublicacaoPremio']) > 90) {
                $mensagem .= "- Existem publicações com mais de 90 dias fruidos.<br/>";
            }
        }

        /*
         * Servidores com licença cadastrada sem informar publicação
         */

        $select2 = "SELECT idLicencaPremio                            
                      FROM tblicencapremio
                     WHERE idServidor = {$idServidor}
                       AND (idPublicacaoPremio is null OR idPublicacaoPremio = 0)";

        $row2 = $pessoal->count($select2);

        if ($row2 > 0) {
            $mensagem .= "- Existem licenças cadastradas sem informar a publicação.<br/>";
        }

        if (!empty($mensagem)) {
            calloutWarning($mensagem, "Ocorrências");
        }
    }

    ###########################################################

    function exibeObsGeral($idServidor) {

        # Pega os Dados
        $mensagem = $this->get_obsGeral($idServidor);

        if (!is_null($mensagem)) {
            calloutWarning($mensagem, "Observação da Licença Prêmio");
        }
    }

    ###########################################################

    function get_dataFinalProximaPeriodo($idServidor) {
        /*
         * Informa a data da próxima publicação
         *
         */

        # Valor fixo do período aquisitivo (em dias) 365 x 5
        $valor = 1825;

        if (is_numeric($idServidor)) {

            # Conecta ao Banco de Dados
            $pessoal = new Pessoal();

            $select = "SELECT dtFimPeriodo
                         FROM tbpublicacaopremio 
                        WHERE idServidor = {$idServidor} 
                     ORDER BY dtFimPeriodo DESC";

            $row = $pessoal->select($select, false);

            if (empty($row[0])) {
                # Anteriormente se calculava usando o número 1825 (365 x 5)
                # Sandra me pediu para não considerar os anos bissextos 
                $dataAntiga = addDias($pessoal->get_dtAdmissao($idServidor), $valor, false);

                # Novo cálculo
                $dataNova = addAnos($pessoal->get_dtAdmissao($idServidor), 5);
                return $dataNova;
            } else {
                # Anteriormente se calculava usando o número 1825 (365 x 5)
                # Sandra me pediu para não considerar os anos bissextos 
                $dataAntiga = addDias(date_to_php($row[0]), $valor + 1, false);

                # Novo cálculo
                $dataNova = addAnos(date_to_php($row[0]), 5);
                return $dataNova;
            }
        } else {
            return null;
        }
    }

    ###########################################################

    function get_dataInicialProximoPeriodo($idServidor) {
        /*
         * Informa a data da próxima publicação
         *
         */

        if (is_numeric($idServidor)) {

            # Conecta ao Banco de Dados
            $pessoal = new Pessoal();

            $select = "SELECT dtFimPeriodo
                         FROM tbpublicacaopremio 
                        WHERE idServidor = {$idServidor} 
                     ORDER BY dtFimPeriodo DESC";

            $row = $pessoal->select($select, false);

            if (empty($row[0])) {
                # quando não tiver é a data de admissão (primeiro período)
                return $pessoal->get_dtAdmissao($idServidor);
            } else {
                return addDias(date_to_php($row[0]), 1, false);
            }
        } else {
            return null;
        }
    }

    ###########################################################

    public function exibeDoerj($idPublicacaoPremio) {
        /**
         * Exibe um link para exibir o pdf do certificado
         * 
         * @param $idFormacao integer null O id
         * 
         * @syntax $formacao->exibeCertificado($idFormacao);
         */
        # Monta o arquivo
        $arquivo = PASTA_PUBLICACAO_PREMIO . $idPublicacaoPremio . ".pdf";

        # Verifica se ele existe
        if (file_exists($arquivo)) {

            # Monta o link
            $link = new Link(null, $arquivo, "Exibe a publicação");
            $link->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
            $link->set_target("_blank");
            $link->show();
        } else {
            echo "---";
        }
    }

    ###########################################################

    public function get_ultimaPublicacaoFruída($idServidor) {
        /**
         * Informa a data inicial da próxima licença
         * 
         * @param $idServidor integer null O id do servidor
         * 
         * @syntax $licenca->proximaData($idServidor);
         */
        /*
         * Pega a última publicação fruída.
         */

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        $select = "SELECT idPublicacaoPremio,                     
                          ADDDATE(dtInicial,tblicencapremio.numDias-1)
                     FROM tblicencapremio 
                    WHERE idServidor = {$idServidor} 
                    ORDER BY dtInicial DESC";

        $row = $pessoal->select($select, false);

        return $row;
    }

###########################################################

    public function get_proximaData($idServidor) {
        /**
         * Informa a data inicial da próxima licença
         * 
         * @param $idServidor integer null O id do servidor
         * 
         * @syntax $licenca->proximaData($idServidor);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        /*
         * Pega a última publicação fruída.
         */

        $proximaPublicacao = $this->get_ultimaPublicacaoFruída($idServidor);

        /*
         * Pega quantos dias foram fruídos com essa publicação
         */

        if (!empty($proximaPublicacao[0])) {

            $select = "SELECT SUM(numDias) 
                     FROM tblicencapremio 
                    WHERE idPublicacaoPremio = {$proximaPublicacao[0]} 
                    ORDER BY dtInicial DESC";

            $numDias = $pessoal->select($select, false)[0];

            if ($numDias < 90) {
                return addDias(addAnos(date_to_php($proximaPublicacao[1]), 1), 2);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    ###########################################################

    public function get_proximoPeriodo($idServidor) {
        /**
         * Informa o período da próxima licença
         * 
         * @param $idServidor integer null O id do servidor
         * 
         * @syntax $licenca->proximaData($idServidor);
         */
        /*
         * Pega a última publicação fruída.
         */

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        $select = "SELECT idPublicacaoPremio,
                          dtTermino
                     FROM tblicencapremio 
                    WHERE idServidor = {$idServidor} 
                    ORDER BY dtInicial DESC";

        $row = $pessoal->select($select, false);

        /*
         * Pega quantos dias foram fruídos com essa publicação
         */

        if (!empty($row[0])) {
            $select = "SELECT CONCAT(DATE_FORMAT(dtInicioPeriodo, '%d/%m/%Y'),' - ',DATE_FORMAT(dtFimPeriodo, '%d/%m/%Y'))
                     FROM tbpublicacaopremio 
                    WHERE idPublicacaoPremio = {$row[0]}";

            $periodo = $pessoal->select($select, false)[0];
            return $periodo;
        } else {
            return null;
        }
    }

    ###########################################################

    public function get_publicacaoPremioDados($idPublicacaoPremio) {
        /**
         * Fornece os dados da publicação
         * 
         * @param $idPublicacaoPremio integer null O id da publicação
         * 
         * @syntax $licenca->get_publicacaoPremioDados($idPublicacaoPremio);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        if (!empty($idPublicacaoPremio)) {
            $select = "SELECT *
                         FROM tbpublicacaopremio 
                        WHERE idPublicacaoPremio = {$idPublicacaoPremio}";

            $row = $pessoal->select($select, false);
            return $row;
        } else {
            return null;
        }
    }

    ###########################################################

    public function get_proximaPublicacaoParaFruir($idServidor) {
        /**
         * Fornece o id da próxima publicação a ser fruída
         * 
         * @param $idServidor integer null O id do servidor
         * 
         * @syntax $licenca->get_proximaPublicacaoParaFruir($idServidor);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        if (!empty($idServidor)) {
            $select = "SELECT idPublicacaoPremio
                         FROM tbpublicacaopremio 
                        WHERE idServidor = {$idServidor}
                         ORDER BY dtInicioPeriodo";

            $row = $pessoal->select($select);

            # Percorre o array
            foreach ($row as $item) {
                # Verifica os dias fruídos para cada publicação
                $numDiasPub = $this->get_numDiasFruidosPorPublicacao($item[0]);
                
                # Verifica se é menos que 90
                if ($numDiasPub < 90 OR empty($numDiasPub)) {                    
                    return $item[0];
                }
            }
        } else {
            return null;
        }
    }

    ###########################################################
}
