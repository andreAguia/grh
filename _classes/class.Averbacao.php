<?php

class Averbacao {

    function getNumDias($idAverbacao) {

        # Verifica se foi informado o id
        if (empty($idAverbacao)) {
            return null;
        }

        # Pega os valores
        $select = "SELECT dtInicial,
                          dtFinal
                     FROM tbaverbacao
                    WHERE idAverbacao = {$idAverbacao}";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        # Retorna
        return getNumDias(date_to_php($dados[0]), date_to_php($dados[1]));
    }

    #####################################################

    function getDiasAnterior15_12_98($idAverbacao) {

        # Verifica se foi informado o id
        if (empty($idAverbacao)) {
            return null;
        }

        # Data a ser verificada 
        $dtAlvo = date_to_bd("15/12/1998");

        # Pega os valores
        $select = "SELECT dtInicial,
                          dtFinal,
                          dias
                     FROM tbaverbacao
                    WHERE idAverbacao = {$idAverbacao}";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        # Verifica se data inicial é maior que mais recente 
        if ($dados[0] > $dtAlvo) {
            return 0;
        } else {
            # Verifica se a data final é anterior a data alvo
            if ($dados[1] < $dtAlvo) {
                return $dados[2];
            } else {
                return getNumDias(date_to_php($dados[0]), date_to_php($dtAlvo));
            }
        }
    }

    #####################################################

    function getTempoAverbadoAntes31_12_21($idServidor) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        }

        # Pega os valores
        $select = "SELECT dtInicial,
                          dtFinal,
                          dias
                     FROM tbaverbacao
                    WHERE idServidor = {$idServidor}
                 ORDER BY dtInicial";

        # Conecta o banco de dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select);

        # Define a variavel de retorno
        $tempo = 0;

        # Define as datas
        $dataAlvo = "31/12/2021";

        # Percorre os registros
        foreach ($row as $itens) {
            # As datas
            $dtInicial = date_to_php($itens["dtInicial"]);
            $dtFinal = date_to_php($itens["dtFinal"]);            
            
            # Verifica se a data Alvo está após o período
            if (dataMenor($dataAlvo, $dtFinal) == $dtFinal) {
                $tempo += $itens["dias"];
            }
            
            # Verifica se a data Alvo está dentro  do período
            if (entre($dataAlvo, $dtInicial, $dtFinal)) {
                $tempo += getNumDias($dtInicial, $dataAlvo);
            }
        }

        return $tempo;
    }

    #####################################################

    function tempoSobreposto($idServidor) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        }

        # Conecta ao Banco de Dados
        $intra = new Intra();
        $pessoal = new Pessoal();
        $aposentadoria = new Aposentadoria();

        # Verifica a data de saída (quando for inativo)
        $dtSaida = $pessoal->get_dtSaida($idServidor);

        # Analisa a data
        if (empty($dtSaida)) {
            # Se for ativo a data vem vazia então analisa até hoje
            $dtFinal = date("Y-m-d");
        } else {
            # Se for inativo analisa até a data de saída
            $dtFinal = date_to_bd($dtSaida);
        }

        ################################################################
        # Verifica se tem Tempo averbado sobreposto
        # Pega as averbações desse servidor
        $select = "SELECT dtInicial, 
                          dtFinal, 
                          idAverbacao 
                     FROM tbaverbacao 
                    WHERE idServidor = {$idServidor}
                 ORDER BY dtInicial";

        $result = $pessoal->select($select);

        # Acrescenta o tempo de UENF
        $dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));
        $result[] = array($dtAdmissao, $dtFinal, null);

        # Inicia a variável que informa se tem sobreposicao
        $sobreposicao = false;

        # Inicia o array que guarda os períodos problemáticos
        $idsProblemáticos[] = null;

        # Percorre os registros
        foreach ($result as $periodo) {
            $dtInicial1 = date_to_php($periodo[0]);
            $dtFinal1 = date_to_php($periodo[1]);
            $idAverbado1 = $periodo[2];

            # Percorre a mesma listagem novamente
            foreach ($result as $periodoVerificado) {

                $dtInicial2 = date_to_php($periodoVerificado[0]);
                $dtFinal2 = date_to_php($periodoVerificado[1]);
                $idAverbado2 = $periodoVerificado[2];

                # Evita que seja comparado com ele mesmo
                if ($idAverbado1 <> $idAverbado2) {
                    if (verificaSobreposicao($dtInicial1, $dtFinal1, $dtInicial2, $dtFinal2)) {
                        $sobreposicao = true;
                        $idsProblemáticos[] = $idAverbado1;
                        $idsProblemáticos[] = $idAverbado2;
                    }
                }
            }
        }

        # Retorna a sobreposição FALSE ou TRUE
        return $sobreposicao;
    }

    ##############################################################################################################################################

    /**
     * Método get_tempoAverbadoPrivado
     * informa o total de dias de tempo averbado em empresa privada
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoAverbadoPrivado($idServidor) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        }

        # Pega os valores
        $select = 'SELECT SUM(dias) as total
                     FROM tbaverbacao
                    WHERE empresaTipo = 2 AND idServidor = ' . $idServidor . '
                         ORDER BY total';

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        $row = $pessoal->select($select, false);

        if (is_null($row[0])) {
            return 0;
        } else {
            return $row[0];
        }
    }

##############################################################################################################################################

    /**
     * Método get_tempoAverbadoPublico
     * informa o total de dias de tempo averbado em empresa Pública
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoAverbadoPublico($idServidor) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        }

        # Pega os valores
        $select = 'SELECT SUM(dias) as total
                     FROM tbaverbacao
                    WHERE empresaTipo = 1 AND idServidor = ' . $idServidor . '
                         ORDER BY total';

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        $row = $pessoal->select($select, false);

        if (is_null($row[0])) {
            return 0;
        } else {
            return $row[0];
        }
    }

##############################################################################################################################################

    /**
     * Método get_tempoAverbadoPublicoRegime
     * informa o total de dias de tempo averbado em empresa Pública por Regime
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoAverbadoPublicoRegime($idServidor, $regime) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        }

        # Pega os valores
        $select = "SELECT SUM(dias) as total
                     FROM tbaverbacao
                    WHERE empresaTipo = 1 
                      AND regime = {$regime}
                      AND idServidor = {$idServidor}
                         ORDER BY total";

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        $row = $pessoal->select($select, false);

        if (is_null($row[0])) {
            return 0;
        } else {
            return $row[0];
        }
    }

##############################################################################################################################################

    /**
     * Método get_tempoAverbadoTotal
     * informa o total de dias de tempo averbado em empresa Pública e privada
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoAverbadoTotal($idServidor) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        }

        # Pega o tempo público
        $publico = $this->get_tempoAverbadoPublico($idServidor);

        # Pega o privado
        $privado = $this->get_tempoAverbadoPrivado($idServidor);

        # Retorno o total
        return $publico + $privado;
    }

##############################################################################################################################################

    /**
     * Método get_temTempoAverbado
     * informa Sim / Não se tem tempo averbado
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_temTempoAverbado($idServidor) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        }

        if ($this->get_tempoAverbadoTotal($idServidor) > 0) {
            return "Sim";
        } else {
            return "Não";
        }
    }

##############################################################################################################################################

    /**
     * Método get_temTempoAverbadoPublico
     * informa Sim / Não se tem tempo público averbado
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_temTempoAverbadoPublico($idServidor) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        }

        if ($this->get_tempoAverbadoPublico($idServidor) > 0) {
            return "Sim";
        } else {
            return "Não";
        }
    }

##############################################################################################################################################
}
