<?php

class Ferias {

    /**
     * Abriga as várias rotina referentes as férias do servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    ###########################################################

    function exibeFeriasPendentes($idServidor) {
        /**
         * Função uma string com as pendências de férias do servidor
         */
        # Pega o ano da aposentadoria compulsória
        $aposentCompulsorio = new AposentadoriaCompulsoria();
        $anoCompulsoria = year($aposentCompulsorio->getDataAposentadoriaCompulsoria($idServidor));

        # Retorno
        $retorno = null;

        # contador de linhas para saber se for mais de um e colocar o br
        $linhas = 0;

        # Verifica se estamos no ano da compulsória
        if (date('Y') >= $anoCompulsoria) {
            # Ano final da tabela
            $anoFinal = $anoCompulsoria;
            $retorno .= "Servidor tem Aposentadoria Compulsória em {$anoCompulsoria} - ({$aposentCompulsorio->getDataAposentadoriaCompulsoria($idServidor)})";
            $linhas++;
        } else {
            # Ano final da tabela
            $anoFinal = date('Y') + 1;
        }

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Pega os dados do servidor
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);
        $anoAdmissao = year($dtAdmissao);

        # As ferias estao cadastradas somente apartir de 2014
        # Sandra pediu para alterar para 2016 para nao exibir alguns problemas
        $feriasCadastradas = 2016;

        # Percorre os anos de trabalho desse servidor
        for ($ano = $anoFinal; $ano >= $anoAdmissao + 1; $ano--) {

            # Verifica se é do período estipulado
            if ($ano >= $feriasCadastradas) {

                # Pega os dias de férias
                $dias = $pessoal->get_feriasSomaDias($ano, $idServidor);

                # Transforma o nulo em zero
                if (is_null($dias)) {
                    $dias = 0;
                }

                # Pega os dias trabalhados
                $diasTrabalhados = $this->get_diasTrabalhados($idServidor, $ano);

                # Verifica se trabalhou, pelo menos, um dia
                if ($diasTrabalhados > 1) {
                    # Verifica se tem pendencia 
                    if ($dias < 30) {
                        $pendencia = 30 - $dias;

                        if ($linhas > 0) {
                            $retorno .= "<br/>";
                        }

                        $retorno .= "Pendentes $pendencia Dias em {$ano}";
                        $linhas++;
                    }
                }
            }
        }

        return $retorno;
    }

###########################################################

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = 'SELECT obs
                     FROM tbferias
                    WHERE idFerias = ' . $id;

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno[0]);
        }
    }

###########################################################

    public function getProcesso($lotacao) {

        /**
         * Retorna o número do processo de férias de uma lotação
         */
        # verifica se foi informado a lotação
        if (empty($lotacao)) {
            return null;
        }

        # Exibe o processo
        if (is_numeric($lotacao)) {
            $pessoal = new Pessoal();
            $lotacao = $pessoal->get_lotacaoDiretoria($lotacao);
        }

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = "SELECT processo
                     FROM tbferiasprocesso
                    WHERE lotacao = '{$lotacao}'";

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            return null;
        } else {
            return $retorno[0];
        }
    }

###########################################################

    public function exibeProcesso($lotacao) {

        # verifica se foi informado a lotação
        if (empty($lotacao)) {
            return null;
        }

        titulotable("Processo de Férias");
        $painel = new Callout();
        $painel->abre();
        p(trataNulo($this->getProcesso($lotacao)), "f16", "center");
        $painel->fecha();
    }

###########################################################

    public function get_diasSolicitadosFruidos($idServidor, $ano = null) {

        /**
         * retorna os dias fruidos, solicitados, etc para um servidor em um exercicio determinado
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Trata os parêmetros
        if (empty($idServidor)) {
            return null;
        }

        if (empty($ano)) {
            $ano = date('Y');
        }

        # Pega array com os dias publicados
        $select = "SELECT SUM(numDias) as dias
                     FROM tbferias
                    WHERE idServidor = {$idServidor}
                      AND anoExercicio = '{$ano}'
                      AND (status = 'fruída' OR status = 'solicitada' OR status = 'confirmada') ";

        $retorno = $pessoal->select($select, false);
        return $retorno["dias"];
    }

###########################################################

    public function get_diasTrabalhados($idServidor, $ano = null) {

        /**
         * Retorna os dias trabalhados pelo servidor em um ano
         */
        # Trata os parêmetros
        if (empty($idServidor) OR empty($ano)) {
            return null;
        }

        # Verifica se o ano é bissexto
        if (anoBissexto($ano)) {
            $diasAno = 366;
        } else {
            $diasAno = 365;
        }

        # Pega os dias afastados
        $verificadias = new VerificaDiasAfastados($idServidor);
        $verificadias->setAno($ano);
        $verificadias->verifica();

        # retorna os dias trabalhados
        return $diasAno - $verificadias->getDiasAfastados();
    }

    ###########################################################

    /**
     * Método get_feriasResumo
     * 
     * Fornece um array com a lista de totais de dias fruidos/solicitados por ano de exercicio
     */
    public function get_feriasResumo($idServidor) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Trata os parêmetros
        if (empty($idServidor)) {
            return null;
        }

        $select = "SELECT anoexercicio, 
                          SUM(numDias) as total                        
                     FROM tbferias
                    WHERE idservidor = {$idServidor}
                      AND (status = 'fruída' OR status = 'solicitada' OR status = 'confirmada')
                 GROUP BY anoexercicio
                 ORDER BY anoexercicio desc";

        $row = $pessoal->select($select);
        $quantos = count($row);

        $novoArray = null;

        # Pega o menor ano cadastrado
        if ($quantos > 0) {
            $menorValor = $row[$quantos - 1];
            $menorAno = $menorValor['anoexercicio'];

            # Pega o ano da aposentadoria compulsória
            $aposentCompulsorio = new AposentadoriaCompulsoria();
            $anoCompulsoria = year($aposentCompulsorio->getDataAposentadoriaCompulsoria($idServidor));

            # Verifica se estamos no ano da compulsória
            if (date('Y') >= $anoCompulsoria) {
                # Pega o ano da compulsoria
                $maiorAno = $anoCompulsoria;
            } else {
                # Pega o maior ano (anoatual +1)
                $maiorAno = date('Y') + 1;
            }

            # Percorre os anos
            for ($ano = $maiorAno; $ano >= $menorAno; $ano--) {

                # Verifica se trabalhou no ano
                if ($this->get_diasTrabalhados($idServidor, $ano) > 0) {

                    # Se o ano não estiver no array acrescenta o ano com valor 0
                    if (array_search($ano, array_column($row, 'anoexercicio')) === false) {
                        $novoArray[] = array($ano, 0, 30);
                    } else {
                        $dias = $pessoal->get_feriasSomaDias($ano, $idServidor);
                        $novoArray[] = array($ano, $dias, 30 - $dias);
                    }
                } else {
                    $novoArray[] = array("<s>{$ano}</s>", "---", "Afastado");
                }
            }
        }

        return $novoArray;
    }

    ########################################################### 

    function exibeFeriasPendentesAteDeterminadoano($texto) {
        /**
         * Função o numero de dias de ferias pendentes de um servidor
         * 
         * Usado no relatorio de escala de ferias
         */
        # Divide o texto idServidor&Ano
        $pedaco = explode("&", $texto);

        # Pega os valores
        $idServidor = $pedaco[0];
        $anoPesquisado = $pedaco[1];

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Pega os dados do servidor
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);    // Data de admissao
        $anoAdmissao = year($dtAdmissao);

        # As ferias estao cadastradas somente apartir de 2014 mas Sandra 
        # pediu para ser a partir de 2016 para nao exibir alguns problemas antigos no cadastro de ferias
        $feriasCadastradas = 2016;

        # Define as variaveis 
        $retorno = null;
        $linhas = 0;

        # Percorre os anos desde a admissão do servidor até o ano da pesquisa
        for ($i = $anoPesquisado; $i >= $anoAdmissao + 1; $i--) {

            # Verifica se o ano pode ser exibido
            if ($i >= $feriasCadastradas) {

                # Verifica se trabalhou no ano
                if ($this->get_diasTrabalhados($idServidor, $i) > 0) {

                    # Verifica dias de férias no período
                    $dias = $this->get_diasSolicitadosFruidos($idServidor, $i);

                    # Verifica se e o ano atual e informa que nao tem mais ferias a serem tiradas esse ano quando ja tirou 30 dias
                    if ($i == $anoPesquisado) {
                        if ($dias > 0) {
                            if ($linhas > 0) {
                                $retorno .= "<br/>";
                            }

                            $retorno .= "Já solicitou os $dias dias de " . $anoPesquisado;
                            $linhas++;
                        }
                    } else {
                        # Verifica se tem pendencia 
                        if ($dias < 30) {
                            $pendencia = 30 - $dias;

                            if ($linhas > 0) {
                                $retorno .= "<br/>";
                            }

                            $retorno .= "Pendentes $pendencia Dias em {$i}";
                            $linhas++;
                        }
                    }
                } else {
                    # Se o servidor não trabalhou então não tem direito a férias neste ano
                    if ($i == $anoPesquisado) {
                        if ($linhas > 0) {
                            $retorno .= "<br/>";
                        }

                        if ($i > date('Y')) {
                            $retorno .= "Servidor Afastado";
                        } else {
                            $retorno .= "Servidor Afastado em {$i}";
                        }
                        $linhas++;
                    }
                }
            }
        }

        # retorna a mensagem
        return $retorno;
    }

###########################################################
}
