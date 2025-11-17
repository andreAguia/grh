<?php

class Afastamentos {
    /**
     * Abriga as várias rotina referentes aos afastamentos no geral 
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */

    #####################################################
    
    
    public function get_tempoAfastamentoSuspendeTempoServicoSemContribuicao($idServidor = null, $dataPrevista = null) {

        # Inicia a variável de retorno
        $retorno = 0;

        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        ######
        # Licença sem vencimentos
        $select = "(SELECT dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                     FROM tblicencasemvencimentos
                    WHERE idServidor = {$idServidor}
                      AND (optouContribuir = 2 OR optouContribuir is NULL))
                    UNION
                    (SELECT dtInicial,
                            numDias,
                            ADDDATE(dtInicial,numDias-1) as dtFinal
                       FROM tblicenca JOIN tbservidor USING (idServidor)
                                      JOIN tbtipolicenca USING (idTpLicenca)
                      WHERE idServidor = {$idServidor}
                        AND tbtipolicenca.tempoServico = 'Sim')
                 ORDER BY 1";

        $result2 = $pessoal->select($select);

        # Percorre e soma os afastamentos
        foreach ($result2 as $item) {
            if (!is_null($dataPrevista)) {

                # Verifica se a data final do afastamento é anterior a data prevista
                if (strtotime($item["dtFinal"]) <= strtotime(date_to_bd($dataPrevista))) {
                    $retorno += $item["numDias"];
                }

                # Verifica se a data final é posterior a data prevista mas a data inicial é anterior
                if (entre($dataPrevista, date_to_php($item["dtInicial"]), date_to_php($item["dtFinal"]), false)) {
                    $retorno += getNumDias(date_to_php($item["dtInicial"]), date_to_php($item["dtFinal"]));
                }
            } else {
                $retorno += $item["numDias"];
            }
        }

        # Retorna o valor calculado
        return $retorno;
    }

    #####################################################  

    public function get_tempoAfastamentoSuspendeTempoServicoComContribuicao($idServidor = null, $dataPrevista = null) {

        # Inicia a variável de retorno
        $retorno = 0;

        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        ######
        # Licença sem vencimentos
        $select = "SELECT dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                     FROM tblicencasemvencimentos
                    WHERE idServidor = {$idServidor}
                      AND optouContribuir = 1
                 ORDER BY dtInicial";

        $result2 = $pessoal->select($select);

        # Percorre e soma os afastamentos
        foreach ($result2 as $item) {
            if (!is_null($dataPrevista)) {

                # Verifica se a data final do afastamento é anterior a data prevista
                if (strtotime($item["dtFinal"]) <= strtotime(date_to_bd($dataPrevista))) {
                    $retorno += $item["numDias"];
                }

                # Verifica se a data final é posterior a data prevista mas a data inicial é anterior
                if (entre($dataPrevista, date_to_php($item["dtInicial"]), date_to_php($item["dtFinal"]), false)) {
                    $retorno += getNumDias(date_to_php($item["dtInicial"]), date_to_php($item["dtFinal"]));
                }
            } else {
                $retorno += $item["numDias"];
            }
        }

        # Retorna o valor calculado
        return $retorno;
    }

    #####################################################    

    /**
     * Método get_tempoAfastamentoSuspendeTempoServico
     * informa o total de dias de afastamento que suspende o tempo de serviço
     * 
     * @param string $idServidor idServidor do servidor
     * @param string $data até a data especificada. Se estiver em branco estipula a data de hoje
     */
    public function get_tempoAfastamentoSuspendeTempoServico($idServidor = null, $dataPrevista = null) {

        # Inicia a variável de retorno
        $retorno = 0;

        # Inicia o banco de Dados
        $pessoal = new Pessoal();

        ######
        # Tabela de Licença sem vencimentos e de licença
        $select = "(SELECT dtInicial,
                          numDias,
                          ADDDATE(dtInicial,numDias-1) as dtFinal
                     FROM tblicencasemvencimentos
                    WHERE idServidor = {$idServidor})
                    UNION
                    (SELECT dtInicial,
                            numDias,
                            ADDDATE(dtInicial,numDias-1) as dtFinal
                       FROM tblicenca JOIN tbservidor USING (idServidor)
                                      JOIN tbtipolicenca USING (idTpLicenca)
                      WHERE idServidor = {$idServidor}
                        AND tbtipolicenca.tempoServico = 'Sim')
                 ORDER BY 1";

        $result2 = $pessoal->select($select);

        # Percorre e soma os afastamentos
        foreach ($result2 as $item) {
            if (!is_null($dataPrevista)) {

                # Verifica se a data final do afastamento é anterior a data prevista
                if (strtotime($item["dtFinal"]) <= strtotime(date_to_bd($dataPrevista))) {
                    $retorno += $item["numDias"];
                }

                # Verifica se a data final é posterior a data prevista mas a data inicial é anterior
                if (entre($dataPrevista, date_to_php($item["dtInicial"]), date_to_php($item["dtFinal"]), false)) {
                    $retorno += getNumDias(date_to_php($item["dtInicial"]), date_to_php($item["dtFinal"]));
                }
            } else {
                $retorno += $item["numDias"];
            }
        }

        # Retorna o valor calculado
        return $retorno;
    }

    ##################################################### 

    /**
     * Método get_tempoAfastamentoComContribuicao
     * informa o total de dias de tempo afastado mas com contribuição
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoAfastamentoComContribuicao($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Licença Sem Vencimentos
        $select2 = "SELECT numDias                           
                      FROM tblicencasemvencimentos
                      WHERE idServidor = {$idServidor}
                        AND optouContribuir = 1";

        # Soma
        return array_sum(array_column($pessoal->select($select2), 'numDias'));
    }

    #####################################################
}
