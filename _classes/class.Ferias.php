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
        # Valores
        $anoPesquisado = date('Y') + 1;

        # Retorno
        $retorno = null;

        # Numero de linhas para saber se for mais de um e colocar o br
        $linhas = 0;

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Pega os dados do servidor
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);
        $anoAdmissao = year($dtAdmissao);

        # As ferias estao cadastradas somente apartir desse ano
        # Entao a busca sera a partir desse ano. Cadastrando-se mais anos
        # Altera-se esse valor
        #$feriasCadastradas = 2014;
        # Atualizaçao: SAndra pediu para ser a partir de 2016 para nao exibir alguns problemas antigos no cadastro de ferias
        $feriasCadastradas = 2016;

        # Monta o retorno
        for ($i = $anoAdmissao + 1; $i <= $anoPesquisado; $i++) {
            if ($i >= $feriasCadastradas) {
                $dias = $pessoal->get_feriasSomaDias($i, $idServidor);
                
                # resolve temporariamente o problema de Simone Flores
                if(($idServidor == 15 and $i == 2020) OR ($idServidor == 15 and $i == 2021)){
                    $dias = 30;
                }

                # Transforma o nullo em zero
                if (is_null($dias)) {
                    $dias = 0;
                }

                # Verifica se e o ano atual e informa que nao tem mais ferias
                # a serem tiradas esse ano quando ja tirou 30 dias
                if ($i == $anoPesquisado) {
                    if ($dias > 0) {
                        if ($linhas > 0) {
                            $retorno .= "<br/>";
                        }

                        if ($dias < 30) {
                            $retorno .= "solicitou apenas $dias dias de " . $anoPesquisado;
                        }
                        $linhas++;
                    }
                } else {
                    # Verifica se tem pendencia 
                    if ($dias < 30) {
                        $pendencia = 30 - $dias;

                        if ($linhas > 0) {
                            $retorno .= "<br/>";
                        }

                        $retorno .= "($i) - pendente $pendencia Dias,";
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

    public function get_feriasFruidasServidorExercicio($idServidor, $ano = null) {

        /**
         * retorna um array com os dias fruidos, solicitados, etc para um servidor em um exercicio determinado
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
                      AND status = 'fruída'";

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
        if(anoBissexto($ano)){
            $diasAno = 366;            
        }else{
            $diasAno = 365;
        }
        
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        

        # Pega array com os dias publicados
        $select = "SELECT SUM(numDias) as dias
                     FROM tbferias
                    WHERE idServidor = {$idServidor}
                      AND anoExercicio = '{$ano}'
                      AND status = 'fruída'";

        $retorno = $pessoal->select($select, false);
        return $retorno["dias"];
    }
}
