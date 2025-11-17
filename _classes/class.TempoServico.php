<?php

class TempoServico {
    /**
     * Abriga as várias rotina referentes ao cálculo de tempo de serviço de um servidor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */

    #####################################################    

    /**
     * Método get_tempoServicoUenfBruto
     * informa o total de dias corridos de tempo de serviço bruto dentro da uenf, 
     * sem os afastamentos.
     * 
     * @param string $idServidor idServidor do servidor
     * @param string $data até a data especificada. Se estiver em branco estipula a data de hoje
     */
    public function get_tempoServicoUenfBruto($idServidor, $data = null) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Data Inicial (data de admissão)
        $dtInicial = $pessoal->get_dtAdmissao($idServidor);

        # Verifica se o servidor é inativo e pega a data de saída dele
        if ($pessoal->get_idSituacao($idServidor) == 1) {

            # verifica a data
            if (empty($data)) {
                // Data final padrão de ativo é hoje
                $data = date("d/m/Y");
            } else {
                // Verifica se a data final é anterior que a data inicial
                if (strtotime(date_to_bd($data)) < strtotime(date_to_bd($dtInicial))) {
                    $data = $dtInicial;
                }
            }
        } else {
            // Pega a data de Saída
            $dtSaida = $pessoal->get_dtSaida($idServidor);

            # verifica a data
            if (empty($data)) {
                // Data final padrão de inativo é a data de saída
                $data = $dtSaida;
            } else {
                // Verifica se a data final está entre o período do servidor na Uenf
                // senão tiver muda para a data de saída
                if (!entre($data, $dtInicial, $dtSaida)) {
                    $data = $dtSaida;
                }
            }
        }

        # Calcula o número de dias
        $numdias = getNumDias($dtInicial, $data);
        return $numdias;
    }

    #####################################################    

    /**
     * Método get_tempoServicoUenfLiquido
     * informa o total de dias corridos de tempo de serviço liquido dentro da uenf, 
     * com os afastamentos.
     * 
     * @param string $idServidor idServidor do servidor
     * @param string $data até a data especificada. Se estiver em branco estipula a data de hoje
     */
    public function get_tempoServicoUenfLiquido($idServidor, $data = null) {

        
        # Pega o tempo bruto
        $numdias = $this->get_tempoServicoUenfBruto($idServidor, $data);

        # Verifica se houve algum afastamento nesse período para descontar
        $afastamentos = new Afastamentos();
        $numDiasAfastamento = $afastamentos->get_tempoAfastamentoSuspendeTempoServico($idServidor, $data);
        
        # Retorna os dias
        return $numdias - $numDiasAfastamento;
    }

    #####################################################  
    
    /**
     * Método get_tempoServicoUenfCeletista
     * informa o total de dias corridos de tempo de serviço celetista dentro da uenf
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_tempoServicoUenfBrutoCeletista($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();
        $concurso = new Concurso();

        # indorma a data em que houve a transformação em estatutário (menos um dia)
        $dataEstatutario = "08/09/2003";

        # Data Inicial (data de admissão)
        $dtInicial = $pessoal->get_dtAdmissao($idServidor);

        # Pega o regime do concurso
        $regime = $concurso->get_regime($pessoal->get_idConcurso($idServidor));

        # Define a data final do período celetista
        if ($regime == "CLT") {
            # Verifica se o servidor é ativo
            if ($pessoal->get_idSituacao($idServidor) == 1) {
                $dtFinal = $dataEstatutario;
            } else {
                # Pega a data de saída
                $dtSaida = $pessoal->get_dtSaida($idServidor);

                # Verifica se foi antes ou depois da transformação
                if (dataMaior($dataEstatutario, $dtSaida) == $dtSaida) {
                    $dtFinal = $dataEstatutario;
                } else {
                    $dtFinal = $dtSaida;
                }
            }
        } else {
            return 0;
        }

        return getNumDias($dtInicial, $dtFinal);
    }

    #####################################################    

    /**
     * Método get_tempoServicoUenfEstatutario
     * informa o total de dias corridos de tempo de serviço estatutario dentro da uenf
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_tempoServicoUenfBrutoEstatutario($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();
        $concurso = new Concurso();

        # define a data em que houve a transformação em estatutário
        $dataEstatutario = "09/09/2003";

        # Data Inicial (data de admissão)
        $dtInicial = $pessoal->get_dtAdmissao($idServidor);

        # Pega a data de saída
        $dtSaida = $pessoal->get_dtSaida($idServidor);

        # Pega o regime do concurso
        $regime = $concurso->get_regime($pessoal->get_idConcurso($idServidor));

        # Define a data final do período celetista
        if ($regime == "CLT") {
            # Verifica se o servidor é ativo ou inativo
            if ($pessoal->get_idSituacao($idServidor) == 1) {
                $dtInicial = $dataEstatutario;
                $dtFinal = date("d/m/Y");
            } else {
                # Verifica se foi antes ou depois da transformação
                if (dataMaior($dataEstatutario, $dtSaida) == $dtSaida) {
                    $dtInicial = $dataEstatutario;
                } else {
                    return 0;
                }
            }
        } else {
            if ($pessoal->get_idSituacao($idServidor) == 1) {
                $dtFinal = date("d/m/Y");
            } else {
                $dtFinal = $dtSaida;
            }
        }

        return getNumDias($dtInicial, $dtFinal);
    }

    ##################################################### 
    
     

    /**
     * Método get_tempoContribuicao
     * Indorma em dias o tempo de contribuição de um servidor 
     * ***** Considerando os afastamentos *****
     * 
     * @param string $idServidor    null idServidor do servidor
     */
    public function get_tempoContribuicao($idServidor = null) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        } else {
            # Inicia as classes
            $averbacao = new Averbacao();

            # Pega o tempo averbado
            $averbado = $averbacao->get_tempoAverbadoTotal($idServidor);

            # Pega o tempo Uenf Bruto
            $tempoServico = new TempoServico();
            $uenfBruto = $tempoServico->get_tempoServicoUenfBruto($idServidor);

            # Retira o tempo dos afastamentos sem contribuição
            $afastamentos = new Afastamentos();
            $afastamento = $afastamentos->get_tempoAfastamentoSuspendeTempoServicoSemContribuicao($idServidor);

            return $averbado + $uenfBruto - $afastamento;
        }
    }
    
    ###########################################################
    
    
    
    
    
    
    
    
    
    

    /**
     * Método get_tempoServicoUenfAntes31_12_21
     * informa o total de dias corridos de tempo de serviço 
     * dentro da uenf antes de 31/12/2021
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_tempoServicoUenfAntes31_12_21($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Define as datas
        $dataAlvo = "31/12/2021";
        $dtInicial = $pessoal->get_dtAdmissao($idServidor);

        # Verifica se a admissão é posterior a data alvo
        if (dataMenor($dataAlvo, $dtInicial) == $dataAlvo) {
            return 0;
        } else {
            # Verifica se o servidor é inativo e pega a data de saída dele
            if ($pessoal->get_idSituacao($idServidor) == 1) {
                $dtFinal = $dataAlvo;
            } else {
                $dtFinal = $pessoal->get_dtSaida($idServidor);

                # Verifica se saiu antes ou depois da data alvo
                if (dataMenor($dataAlvo, $dtFinal) == $dataAlvo) {
                    $dtFinal = $dataAlvo;
                }
            }
        }

        # Pega o tempo sem contribuição
        $tempoRetirar = $this->get_tempoUenfInterrompidoAntes31_12_21($idServidor);
        

        return getNumDias($dtInicial, $dtFinal) - $tempoRetirar;
    }

    #####################################################        

    /**
     * Método get_tempoServicoUenfAntesDataAlvo
     * informa o total de dias corridos de tempo de serviço 
     * dentro da uenf antes de uma data alvo
     * 
     * @param string $idServidor idServidor do servidor
     */
    public function get_tempoServicoUenfAntesDataAlvo($idServidor = null, $dataAlvo = null) {

        # Verifica se foi informado os parâmetros
        if (empty($idServidor) OR empty($dataAlvo)) {
            return null;
        }

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Data inicial
        $dtInicial = $pessoal->get_dtAdmissao($idServidor);

        # Verifica se a admissão é posterior a data alvo
        if (dataMenor($dataAlvo, $dtInicial) == $dataAlvo) {
            return 0;
        } else {
            # Verifica se o servidor é inativo e pega a data de saída dele
            if ($pessoal->get_idSituacao($idServidor) == 1) {
                $dtFinal = $dataAlvo;
            } else {
                $dtFinal = $pessoal->get_dtSaida($idServidor);

                # Verifica se saiu antes ou depois da data alvo
                if (dataMenor($dataAlvo, $dtFinal) == $dataAlvo) {
                    $dtFinal = $dataAlvo;
                }
            }
        }

        # Pega o tempo sem contribuição
        $tempoRetirar = $this->get_tempoUenfInterrompidoAntes31_12_21($idServidor);

        return getNumDias($dtInicial, $dtFinal) - $tempoRetirar;
    }

    #####################################################
    
    

    /**
     * Método get_tempoPublicoIninterrupto
     * informa em dias o tempo publico ininterrupto
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoPublicoIninterrupto($idServidor) {

        # Conecta o banco de dados
        $pessoal = new Pessoal();

        # Pega a data de Ingresso
        $aposentadoria = new Aposentadoria();
        $dtIngresso = $aposentadoria->get_dtIngressoParaTempoPublico($idServidor);

        # Pega os dados
        $select = "SELECT dtInicial,
                          dias
                     FROM tbaverbacao
                    WHERE empresaTipo = 1 AND idServidor = {$idServidor}
                 ORDER BY dtInicial DESC";

        $result = $pessoal->select($select);
        
        $afastamentos = new Afastamentos();
        $totalDias = $this->get_tempoServicoUenfBruto($idServidor) - $afastamentos->get_tempoAfastamentoComContribuicao($idServidor);

        # Percorre o arquivo de averbação para pegar os dias digitados (e não calculados)
        foreach ($result as $periodo) {

            # Se a data inicial do tempo averbado for igual ou maior que a data
            # de ingresso então acrescenta os dias
            if (strtotime(date_to_bd($dtIngresso)) <= strtotime($periodo[0])) {
                $totalDias += $periodo[1];
            }
        }

        return $totalDias;
    }

    #####################################################

    /**
     * Método get_tempoTotal
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoTotal($idServidor) {

        $averbacao = new Averbacao();

        # Pega o tempo averbado total
        $tempoAverbado = $averbacao->get_tempoAverbadoTotal($idServidor);

        # Pega o tempo Uenf
        $tempoServico = new TempoServico();
        $tempoUenf = $tempoServico->get_tempoServicoUenfBruto($idServidor);

        return $tempoAverbado + $tempoUenf;
    }

    #####################################################

    /**
     * Método get_tempoTotal
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoTotalAntes31_12_21($idServidor) {

        $averbacao = new Averbacao();
        
        # Pega o tempo averbado
        $tempoAverbado = $averbacao->getTempoAverbadoAntes31_12_21($idServidor);
        
        # Pega o tempo uenf
        $tempoUenf = $this->get_tempoServicoUenfAntes31_12_21($idServidor);

        return $tempoUenf + $tempoAverbado;
    }

    #####################################################

    /**
     * Método get_tempoTotal
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_tempoTotalAntesDataAlvo($idServidor = null, $dataAlvo = null) {

        # Verifica se foi informado os parâmetros
        if (empty($idServidor) OR empty($dataAlvo)) {
            return null;
        }

        $averbacao = new Averbacao();
        $tempoUenf = $this->get_tempoServicoUenfAntesDataAlvo($idServidor, $dataAlvo);
        $tempoAverbado = $averbacao->getTempoAverbadoAntesDataAlvo($idServidor, $dataAlvo);

        return $tempoUenf + $tempoAverbado;
    }

    #####################################################

    /**
     * Método get_data20anosPublicos
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_data20anosPublicos($idServidor) {

        $dtIngresso = $this->get_dtIngressoParaTempoPublico($idServidor);
        return day($dtIngresso) . "/" . month($dtIngresso) . "/" . (year($dtIngresso) + 20);
    }

    #####################################################

    /**
     * Método get_data10anosPublicos
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_data10anosPublicos($idServidor) {

        $dtIngresso = $this->get_dtIngressoParaTempoPublico($idServidor);
        return day($dtIngresso) . "/" . month($dtIngresso) . "/" . (year($dtIngresso) + 10);
    }

    #####################################################

    /**
     * Método get_data25anosPublicos
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_data25anosPublicos($idServidor) {

        $dtIngresso = $this->get_dtIngressoParaTempoPublico($idServidor);
        return day($dtIngresso) . "/" . month($dtIngresso) . "/" . (year($dtIngresso) + 25);
    }

    #####################################################

    /**
     * Método get_data30anosPublicos
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_data30anosPublicos($idServidor) {

        $dtIngresso = $this->get_dtIngressoParaTempoPublico($idServidor);
        return day($dtIngresso) . "/" . month($dtIngresso) . "/" . (year($dtIngresso) + 30);
    }

    #####################################################

    /**
     * Método get_data35anosPublicos
     * informa em dias o tempo total do servidor
     * 
     * @param	string $idServidor idServidor do servidor
     */
    public function get_data35anosPublicos($idServidor) {

        $dtIngresso = $this->get_dtIngressoParaTempoPublico($idServidor);
        return day($dtIngresso) . "/" . month($dtIngresso) . "/" . (year($dtIngresso) + 35);
    }

    #####################################################

    function get_tempoUenfInterrompidoAntes31_12_21($idServidor) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        }

        # Licença Geral
        $select = "(SELECT dtInicial,
                           dtTermino,
                           numDias
                      FROM tblicenca JOIN tbtipolicenca USING(idTpLicenca)
                     WHERE idServidor = {$idServidor}
                       AND tbtipolicenca.tempoServico = 'Sim'
                   ) UNION (
                    SELECT dtInicial,
                           dtTermino,
                           numDias                           
                      FROM tblicencasemvencimentos
                      WHERE idServidor = {$idServidor}
                        AND (optouContribuir = 2 OR optouContribuir is NULL)
                        ) ORDER BY 1";

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
            $dtFinal = date_to_php($itens["dtTermino"]);

            # Verifica se a data Alvo está após o período
            if (dataMenor($dataAlvo, $dtFinal) == $dtFinal) {
                $tempo += $itens["numDias"];
            }

            # Verifica se a data Alvo está dentro  do período
            if (entre($dataAlvo, $dtInicial, $dtFinal)) {
                $tempo += getNumDias($dtInicial, $dataAlvo);
            }
        }

        return $tempo;
    }

    #####################################################

    function get_tempoUenfInterrompidoAntesDataAlvo($idServidor = null, $dataAlvo = null) {

        # Verifica se foi informado os parâmetros
        if (empty($idServidor) OR empty($dataAlvo)) {
            return null;
        }

        # Licença Geral
        $select = "(SELECT dtInicial,
                           dtTermino,
                           numDias
                      FROM tblicenca JOIN tbtipolicenca USING(idTpLicenca)
                     WHERE idServidor = {$idServidor}
                       AND tbtipolicenca.tempoServico = 'Sim'
                   ) UNION (
                    SELECT dtInicial,
                           dtTermino,
                           numDias                           
                      FROM tblicencasemvencimentos
                      WHERE idServidor = {$idServidor}
                        AND (optouContribuir = 2 OR optouContribuir is NULL)
                        ) ORDER BY 1";

        # Conecta o banco de dados
        $pessoal = new Pessoal();
        $row = $pessoal->select($select);

        # Define a variavel de retorno
        $tempo = 0;

        # Percorre os registros
        foreach ($row as $itens) {
            # As datas
            $dtInicial = date_to_php($itens["dtInicial"]);
            $dtFinal = date_to_php($itens["dtTermino"]);

            # Verifica se a data Alvo está após o período
            if (dataMenor($dataAlvo, $dtFinal) == $dtFinal) {
                $tempo += $itens["numDias"];
            }

            # Verifica se a data Alvo está dentro  do período
            if (entre($dataAlvo, $dtInicial, $dtFinal)) {
                $tempo += getNumDias($dtInicial, $dataAlvo);
            }
        }

        return $tempo;
    }
    ###########################################################
}