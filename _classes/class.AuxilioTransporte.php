<?php

class AuxilioTransporte {
    /**
     * Abriga as várias rotina referentes ao cadastro de atestado do servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    ###########################################################

    /**
     * Função que retorna o afastamento atual de um servidor (se houver)
     * Obs esta função acessa a classe verifica afastamento
     */
    public function exibeSituacao($idServidor, $mes, $ano) {

        # Verifica se o id foi informado
        if (empty($idServidor)) {
            return null;
        } else {
            # Inicia o banco de Dados
            $pessoal = new Pessoal();

            $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);

            $data = "{$ano}-{$mes}-01";
            $contador = 0;

            /*
             *  Férias
             */

            # Monta o select
            $select = "SELECT idFerias, 
                          anoExercicio,
                          dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                     FROM tbferias
                    WHERE idServidor = {$idServidor}
                      AND (('{$data}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1))
                       OR  (LAST_DAY('{$data}') BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1))
                       OR  ('{$data}' < dtInicial AND LAST_DAY('{$data}') > ADDDATE(dtInicial,numDias-1))
                       OR   (LAST_DAY('{$data}') >= dtInicial AND numDias IS NULL)) 
                 ORDER BY dtInicial";

            # Pega os dados
            $afast = $pessoal->select($select, false);

            # Verifica se tem dados
            if (!empty($afast)) {
                pLista(
                        "Férias",
                        "Exercício {$afast['anoExercicio']}",
                        date_to_php($afast['dtInicial']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['numDias'] . " dias"
                );
                $contador++;
            }

            /*
             *  Licenças e Afastamentos gerais
             */
            $select = "SELECT idLicenca, 
                          tbtipolicenca.nome,
                          dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                 FROM tblicenca JOIN tbtipolicenca USING (idTpLicenca)
                WHERE idServidor = {$idServidor}
                AND (('{$data}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1))
                       OR  (LAST_DAY('{$data}') BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1))
                       OR  ('{$data}' < dtInicial AND LAST_DAY('{$data}') > ADDDATE(dtInicial,numDias-1))
                       OR   (LAST_DAY('{$data}') >= dtInicial AND numDias IS NULL)) 
                 ORDER BY dtInicial";

            # Pega os dados
            $afast = $pessoal->select($select, false);

            # Verifica se tem dados
            if (!empty($afast)) {

                # Verifica se é Licença ou afastamento
                if (mb_stripos($afast['nome'], 'Afastamento') === false) {
                    $afastamento = "Licença";
                } else {
                    $afastamento = "Afastamento";
                }

                # Trata o período
                if (empty($afast['numDias'])) {
                    $periodo = date_to_php($afast['dtInicial']) . " a ???";
                } else {
                    $periodo = date_to_php($afast['dtInicial']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['numDias'] . " dias";
                }

                # Verifica se tem que colocar o hr
                if ($contador > 0) {
                    hr("alerta");
                }
                pLista(
                        $afastamento,
                        $afast['nome'],
                        $periodo
                );
                $contador++;
            }

            /*
             *  Licenças prêmio
             */
            $select = "SELECT idLicencaPremio,
                          dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                 FROM tblicencapremio
                WHERE idServidor = {$idServidor}
                AND (('{$data}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1))
                       OR  (LAST_DAY('{$data}') BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1))
                       OR  ('{$data}' < dtInicial AND LAST_DAY('{$data}') > ADDDATE(dtInicial,numDias-1))
                       OR   (LAST_DAY('{$data}') >= dtInicial AND numDias IS NULL)) 
                 ORDER BY dtInicial";

            # Pega os dados
            $afast = $pessoal->select($select, false);

            # Verifica se tem dados
            if (!empty($afast)) {

                # Verifica se tem que colocar o hr
                if ($contador > 0) {
                    hr("alerta");
                }

                # Trata o período
                if (empty($afast['numDias'])) {
                    $periodo = date_to_php($afast['dtInicial']) . " a ???";
                } else {
                    $periodo = date_to_php($afast['dtInicial']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['numDias'] . " dias";
                }

                pLista(
                        "Licença",
                        "Licença Prêmio",
                        $periodo
                );
                $contador++;
            }

            /*
             *  Licenças sem vencimentos
             */
            $select = "SELECT idLicencaSemVencimentos, 
                          tbtipolicenca.nome,
                          dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                     FROM tblicencasemvencimentos JOIN tbtipolicenca USING (idTpLicenca)
                    WHERE idServidor = {$idServidor}
                AND (('{$data}' BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1))
                       OR  (LAST_DAY('{$data}') BETWEEN dtInicial AND ADDDATE(dtInicial,numDias-1))
                       OR  ('{$data}' < dtInicial AND LAST_DAY('{$data}') > ADDDATE(dtInicial,numDias-1))
                       OR   (LAST_DAY('{$data}') >= dtInicial AND numDias IS NULL)) 
                 ORDER BY dtInicial";

            # Pega os dados
            $afast = $pessoal->select($select, false);

            # Verifica se tem dados
            if (!empty($afast)) {

                # Verifica se tem que colocar o hr
                if ($contador > 0) {
                    hr("alerta");
                }

                # Trata o período
                if (empty($afast['numDias'])) {
                    $periodo = date_to_php($afast['dtInicial']) . " a ???";
                } else {
                    $periodo = date_to_php($afast['dtInicial']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['numDias'] . " dias";
                }

                pLista(
                        "Licença",
                        $afast['nome'],
                        $periodo
                );
                $contador++;
            }

            /*
             *  Faltas Abonadas por atestado
             */
            $select = "SELECT idAtestado,
                          dtInicio,
                          numDias,
                          ADDDATE(dtInicio,numDias-1) as dtFinal
                 FROM tbatestado
                WHERE idServidor = {$idServidor}
                AND (('{$data}' BETWEEN dtInicio AND ADDDATE(dtInicio,numDias-1))
                       OR  (LAST_DAY('{$data}') BETWEEN dtInicio AND ADDDATE(dtInicio,numDias-1))
                       OR  ('{$data}' < dtInicio AND LAST_DAY('{$data}') > ADDDATE(dtInicio,numDias-1))
                       OR   (LAST_DAY('{$data}') >= dtInicio AND numDias IS NULL)) 
                 ORDER BY dtInicio";

            # Pega os dados
            $afast = $pessoal->select($select, false);

            # Verifica se tem dados
            if (!empty($afast)) {

                # Verifica se tem que colocar o hr
                if ($contador > 0) {
                    hr("alerta");
                }

                # Trata o período
                if (empty($afast['numDias'])) {
                    $periodo = date_to_php($afast['dtInicio']) . " a ???";
                } else {
                    $periodo = date_to_php($afast['dtInicio']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['numDias'] . " dias";
                }

                pLista(
                        "Falta Abonada",
                        "Atestado Médico",
                        $periodo
                );
                $contador++;
            }

            /*
             *  Trabalho TRE
             */
            $select = "SELECT idTrabalhoTre,
                          data,
                          dias,
                          ADDDATE(data,dias-1) as dtFinal
                    FROM tbtrabalhotre
                 WHERE idServidor = {$idServidor}
                AND (('{$data}' BETWEEN data AND ADDDATE(data,dias-1))
                       OR  (LAST_DAY('{$data}') BETWEEN data AND ADDDATE(data,dias-1))
                       OR  ('{$data}' < data AND LAST_DAY('{$data}') > ADDDATE(data,dias-1))
                       OR   (LAST_DAY('{$data}') >= data AND dias IS NULL)) 
                 ORDER BY data";

            # Pega os dados
            $afast = $pessoal->select($select, false);

            # Verifica se tem dados
            if (!empty($afast)) {

                # Verifica se tem que colocar o hr
                if ($contador > 0) {
                    hr("alerta");
                }

                # Trata o período
                if (empty($afast['dias'])) {
                    $periodo = date_to_php($afast['data']) . " a ???";
                } else {
                    $periodo = date_to_php($afast['data']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['dias'] . " dias";
                }

                pLista(
                        "TRE",
                        "Trabalhando no TRE",
                        $periodo
                );
                $contador++;
            }

            /*
             *  Folgas TRE
             */
            $select = "SELECT idFolga,
                          data,
                          dias,
                          ADDDATE(data,dias-1) as dtFinal
                 FROM tbfolga
                WHERE idServidor = {$idServidor}
                AND (('{$data}' BETWEEN data AND ADDDATE(data,dias-1))
                       OR  (LAST_DAY('{$data}') BETWEEN data AND ADDDATE(data,dias-1))
                       OR  ('{$data}' < data AND LAST_DAY('{$data}') > ADDDATE(data,dias-1))
                       OR   (LAST_DAY('{$data}') >= data AND dias IS NULL)) 
                 ORDER BY data";

            # Pega os dados
            $afast = $pessoal->select($select, false);

            # Verifica se tem dados
            if (!empty($afast)) {

                # Verifica se tem que colocar o hr
                if ($contador > 0) {
                    hr("alerta");
                }

                # Trata o período
                if (empty($afast['dias'])) {
                    $periodo = date_to_php($afast['data']) . " a ???";
                } else {
                    $periodo = date_to_php($afast['data']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['dias'] . " dias";
                }

                pLista(
                        "Folga",
                        "Em folga do TRE",
                        $periodo
                );
                $contador++;
            }

            /*
             * Licença Médica Sem Alta
             */

            # Verifica se o servidor está em licença médica vencida sem alta
            $select = "SELECT idLicenca, 
                          alta, 
                          dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                      FROM tblicenca
                     WHERE idServidor = {$idServidor}
                       AND (idTpLicenca = 1 OR idTpLicenca = 2 OR idTpLicenca = 30)
                       AND dtInicial <= LAST_DAY('{$data}')
                  ORDER BY dtInicial DESC LIMIT 1";

            # Pega os dados
            $afast = $pessoal->select($select, false);

            # Verifica se tem dados
            if (!empty($afast)) {

                # Verifica se é sem alta
                if ($afast["alta"] <> 1) {

                    # Verifica se tem que colocar o hr
                    if ($contador > 0) {
                        hr("alerta");
                    }

                    # Trata o período
                    if (empty($afast['dias'])) {
                        $periodo = date_to_php($afast['dtInicial']) . " a ???";
                    } else {
                        $periodo = date_to_php($afast['dtInicial']) . " a " . date_to_php($afast['dtFinal']) . " - " . $afast['numDias'] . " dias";
                    }

                    pLista(
                            "Licença Em Aberto",
                            "Licença Médica Sem Alta",
                            $periodo
                    );
                    $contador++;
                }
            }

            /*
             * Acumulação
             */

            # Verifica se o servidor tem acumulação
            $select = "SELECT idAcumulacao,
                              instituicao,
                              cargo,                                     
                              matricula,
                              dtAdmissao,
                              dtSaida
                         FROM tbacumulacao
                        WHERE idServidor = {$idServidor}";

            # Pega os dados
            $acumulacao = $pessoal->select($select, false);

            # Verifica se tem dados
            if (!empty($acumulacao)) {

                if (empty($acumulacao["instituicao"])) {
                    return null;
                } else {

                    # Verifica se tem que colocar o hr
                    if ($contador > 0) {
                        hr("alerta");
                    }

                    # Monta a terceira linha
                    $tercLinha = "Matrícula: {$acumulacao['matricula']}";

                    if (!empty($acumulacao['dtAdmissao'])) {
                        $tercLinha .= " / Admissão: " . date_to_php($acumulacao['dtAdmissao']);
                    }

                    if (!empty($acumulacao['dtSaida'])) {
                        $tercLinha .= " / Aposentadoria: " . date_to_php($acumulacao['dtSaida']);
                    }

                    pLista(
                            "Acumulação de Cargos",
                            $acumulacao["instituicao"],
                            $acumulacao["cargo"],
                            $tercLinha
                    );
                    $contador++;
                }
            }

            /*
             * Cessão
             */

            # Verifica se o servidor tem acumulação
            $select = "SELECT idHistCessao,
                              dtInicio,
                              dtFim,
                              orgao,
                              idHistCessao,
                              processo,
                              obs
                         FROM tbhistcessao
                        WHERE idServidor = {$idServidor}
                          AND (('{$data}' BETWEEN dtInicio AND dtFim)
                           OR  (LAST_DAY('{$data}') BETWEEN dtInicio AND dtFim)
                           OR  ('{$data}' < dtInicio AND LAST_DAY('{$data}') > dtFim)
                           OR  (LAST_DAY('{$data}') > dtInicio AND dtFim IS NULL)    
                             )";

            # Pega os dados
            $cessao = $pessoal->select($select, false);

            # Verifica se tem dados
            if (!empty($cessao)) {

                # Verifica se tem que colocar o hr
                if ($contador > 0) {
                    hr("alerta");
                }

                pLista(
                        "Servidor Cedido",
                        $cessao["orgao"],
                        date_to_php($cessao["dtInicio"]) . " a " . date_to_php($cessao["dtFim"]),
                        $cessao["processo"]
                );
                $contador++;
            }

            /*
             * cedido que retornou mês anterior
             */

            # Verifica se o servidor tem acumulação
            $select = "SELECT idHistCessao,
                              dtInicio,
                              dtFim,
                              orgao,
                              idHistCessao,
                              processo,
                              obs
                         FROM tbhistcessao
                        WHERE idServidor = {$idServidor}
                          AND dtFim BETWEEN DATE_SUB('{$data}', INTERVAL 1 MONTH) AND '{$data}'";

            # Pega os dados
            $cessao = $pessoal->select($select, false);

            # Verifica se tem dados
            if (!empty($cessao)) {

                # Verifica se tem que colocar o hr
                if ($contador > 0) {
                    hr("alerta");
                }

                pLista(
                        "Servidor terminou cessão a menos de 1 mês",
                        $cessao["orgao"],
                        date_to_php($cessao["dtInicio"]) . " a " . date_to_php($cessao["dtFim"]),
                        $cessao["processo"]
                );
                $contador++;
            }
        }
    }

    ###########################################################

    /**
     * Função que informa se o servidor recebeu ou não o auxílio
     */
    public function exibeRecebeu($idServidor, $mes, $ano) {

        # Verifica se o id foi informado
        if (empty($idServidor)) {
            return null;
        } else {
            # Inicia o banco de Dados
            $pessoal = new Pessoal();

            $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);

            # Pega os dasos
            $select = "SELECT idtransporte
                         FROM tbtransporte
                        WHERE idServidor = {$idServidor}
                          AND ano = '{$ano}'
                          AND mes = '{$mes}'";

            # Pega os dados
            $count = $pessoal->count($select);

            # Return
            if ($count > 0) {
                label("Sim", "success", null, "O Nome desse servidor FOI informado na listagem dos que receberam o auxílio este mês");
            } else {
                label("Não", "alert", null, "O Nome desse servidor NÃO foi informado na listagem dos que receberam o auxílio este mês");
            }
        }
    }

    ###########################################################

    /**
     * Informa se houve upload deste mês
     */
    public function houveUpload($mes, $ano) {

        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);

        # Pega os dasos
        $select = "SELECT idtransporte
                         FROM tbtransporte
                        WHERE ano = '{$ano}'
                          AND mes = '{$mes}'";

        # Return
        if ($pessoal->count($select) > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    ###########################################################

    /**
     * Informa se houve upload deste mês
     */
    public function exibeResumo($lotacao = null, $mes = null, $ano = null) {

        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);

        # Verifica se tem algum inativo que recebeu
        $selectInativos = "SELECT tbtransporte.idServidor
                     FROM tbtransporte LEFT JOIN tbservidor USING (idServidor)
                                       JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                       JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                      AND tbtransporte.idServidor IS NOT NULL 
                      AND situacao <> 1
                      AND ano = '{$ano}'
                      AND mes = '{$mes}'";

        # lotacao
        if (!empty($lotacao) and $lotacao <> "todos") {
            if (is_numeric($lotacao)) {
                $selectInativos .= " AND (tblotacao.idlotacao = '{$lotacao}')";
            } else {
                $selectInativos .= " AND (tblotacao.DIR = '{$lotacao}')";
            }
        }

        $receberamInativos = $pessoal->count($selectInativos);

        # Pega os dasos
        $select = "SELECT tbtransporte.idServidor
                     FROM tbtransporte JOIN tbservidor USING (idServidor)
                                       JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                       JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                      AND tbtransporte.idServidor IS NOT NULL 
                      AND situacao = 1
                      AND ano = '{$ano}'
                      AND mes = '{$mes}'";

        # lotacao
        if (!empty($lotacao) and $lotacao <> "todos") {
            $servidores = $pessoal->get_numServidoresAtivos($lotacao);
            # Verifica se o que veio é numérico
            if (is_numeric($lotacao)) {
                $select .= " AND (tblotacao.idlotacao = '{$lotacao}')";
                $titulo = $pessoal->get_nomeLotacao($lotacao);
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$lotacao}')";
                $titulo = $lotacao;
            }
        } else {
            $titulo = "Resumo Geral";
            $servidores = $pessoal->get_numServidoresAtivos();
        }

        $receberamAtivos = $pessoal->count($select);
        $nreceberam = $servidores - $receberamAtivos;

        $array[] = ["Não", $nreceberam, 0, $nreceberam];
        $array[] = ["Sim", $receberamAtivos, $receberamInativos, $receberamAtivos + $receberamInativos];

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($array);
        $tabela->set_label(["Receberam", "Ativos", "Inativos", "Total"]);
        $tabela->set_totalRegistro(false);
        #$tabela->set_align(["left"]);
        $tabela->set_titulo($titulo);
        $tabela->set_colunaSomatorio([1, 2, 3]);
        $tabela->show();
    }

    ###########################################################
}
