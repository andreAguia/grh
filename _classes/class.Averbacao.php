<?php

class Averbacao {

    function getNumDias($idAverbacao) {

        # Pega os dados
        $select = "SELECT dtInicial,
                         dtFinal
                   FROM tbaverbacao
                  WHERE idAverbacao = {$idAverbacao}";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        $retorno = getNumDias($dados[0], $dados[1]);

        return $retorno;
    }

    #####################################################

    function tempoSobreposto($idServidor) {

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
}
