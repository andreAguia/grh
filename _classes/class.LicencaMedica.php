<?php

class LicencaMedica {

    /**
     * Abriga as várias rotina referentes a licença médica de servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
##############################################################

    public function exibeBim($idLicenca = null) {
        # Verifica se o id foi informado
        if (empty($idLicenca)) {
            return "---";
        } else {
            # Pega o tipo de licença
            $pessoal = new Pessoal();
            $tipo = $pessoal->get_tipoLicenca($idLicenca);

            if (!empty($tipo)) {
                # Verifica se esse tipo tem perícia
                if ($pessoal->get_licencaPericia($tipo) == "Sim") {

                    # Monta o arquivo
                    $arquivo = PASTA_BIM . "{$idLicenca}.pdf";

                    # Verifica se ele existe
                    if (file_exists($arquivo)) {

                        $botao = new BotaoGrafico();
                        $botao->set_label($this->getnumBim($idLicenca));
                        $botao->set_url($arquivo);
                        $botao->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
                        $botao->set_title("Exibe o Bim arquivado");
                        $botao->set_target("_blank");
                        $botao->show();
                    } else {

//                        $botao = new BotaoGrafico();
//                        $botao->set_label($this->getnumBim($idLicenca));
//                        $botao->set_url("?fase=uploadBim&id={$idLicenca}&tipo=novo");
//                        $botao->set_imagem(PASTA_FIGURAS . 'upload.png', 20, 20);
//                        $botao->set_title("Faz o Upload do Bim");
//                        $botao->show();
                        return "Sem PDF<br/>" . $this->getnumBim($idLicenca);
                    }
                } else {
                    return "---";
                }
            } else {
                return "---";
            }
        }
    }

###########################################################

    /**
     * Método getnumBim
     * informa o número do Bim
     * 
     * @param	integer $idLicenca id da licença
     */
    function getnumBim($idLicenca) {
        $select = "SELECT num_Bim
                     FROM tblicenca
                    WHERE idLicenca = {$idLicenca}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);
        return $row[0];
    }

    ###########################################################

    /**
     * Método getDtIniciaLicencaAberto
     * informa a data Inicial de uma licença em aberto
     * 
     * @param	integer $idLicenca id da licença
     * @param	integer $ano anterior a esse ano
     */
    function getDtIniciaLicencaAberto($idServidor, $ano) {
        # Variáveis
        $dtInicioLicenca = null;

        # Pega todas as licenças médicas do servidor
        # tipo 1 - Artigo 110 - Licença para tratamento de saúde - Inicial
        # tipo 2 - Artigo 117 - Licença por motivo de doença em pessoa da família
        # tipo 30 - Artigo 111 - Licença para tratamento de saúde - Prorrogação
        $select = "SELECT alta, 
                          dtInicial,
                          idTpLicenca
                     FROM tblicenca
                    WHERE idServidor = {$idServidor}
                      AND year(dtInicial) <= {$ano}  
                      AND (idTpLicenca = 1 OR idTpLicenca = 2 OR idTpLicenca = 30)
                 ORDER BY dtInicial";

        $pessoal = new Pessoal();
        $afast = $pessoal->select($select);

        # Percorre todos os registros
        foreach ($afast as $item) {

            # Verifica se é inicial
            if ($item["idTpLicenca"] == 1 OR $item["idTpLicenca"] == 2) {

                # Se For com alta
                if ($item["alta"] == 1) {
                    # Apaga as variáveis
                    $dtInicioLicenca = null;
                } else {
                    # Preenche a variável com a data inicial
                    $dtInicioLicenca = $item["dtInicial"];
                }
            }

            # Verifica se é prorrogação
            if ($item["idTpLicenca"] == 30 OR $item["idTpLicenca"] == 2) {

                # Verifica se é de alta
                if ($item["alta"] == 1) {
                    # Apaga as variáveis
                    $dtInicioLicenca = null;
                }
            }
        }

        # Retorna a data
        return $dtInicioLicenca;
    }

    ###########################################################
}
