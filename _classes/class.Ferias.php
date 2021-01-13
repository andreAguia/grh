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

##########################################################
}
