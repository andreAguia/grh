<?php

class Formacao {

    /**
     * Abriga as várias rotina do Cadastro de Formação Escolar do servidor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    function get_dados($id) {

        /**
         * Fornece os todos os dados de um id
         */
        # Pega os dados
        $select = "SELECT *
                     FROM tbformacao
                    WHERE idFormacao = {$id}";

        $pessoal = new Pessoal();
        return $pessoal->select($select, false);
    }

    ###########################################################

    function exibeCurso($id) {

        /**
         * Fornece Detalhes do curso
         */
        # Verifica se tem id
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);

            # Trata carga horária
            if (!empty($dados['horas'])) {
                $dados['horas'] .= " horas";
            }

            pLista(
                    $dados['habilitacao'],
                    $dados['instEnsino'],
                    $dados['anoTerm'],
                    $dados['horas']
            );
        }
    }

    ###########################################################

    function get_escolaridade($idServidor) {
        /**
         * Fornece a escolaridade de um servidor seja pelo cargo, 
         * seja pelo cadastro de formação. o que tiver maior escolaridade
         */
        # inicia as variáveis
        $idEscolaridade = 0;

        # Conecta ao banco de dados
        $pessoal = new Pessoal();

        # Pega o idPessoa desse servidor
        $idPessoa = $pessoal->get_idPessoa($idServidor);

        # Pega o id cargo do servidor
        $idCargo = $pessoal->get_idCargo($idServidor);

        # Pega o cargo específico
        if (!empty($idCargo)) {
            $idTipoCargo = $pessoal->get_idTipoCargo($idCargo);

            # Pega a escolaridade do cargo
            switch ($idTipoCargo) {

                # Professorea
                case 1:
                case 2:
                    $idEscolaridade = 11;
                    break;

                # Profissional de Nível Superior
                case 3:
                    $idEscolaridade = 8;
                    break;

                # Profissional de Nível Médio
                case 4:
                    $idEscolaridade = 6;
                    break;

                # Profissional de Nível Fundamental
                case 5:
                    $idEscolaridade = 4;
                    break;

                # Profissional de Nível Elementar
                case 6:
                    $idEscolaridade = 2;
                    break;

                default:
                    $idEscolaridade = 0;
                    break;
            }
        }

        # Pega a escolaridade da tabela formação
        $select = "SELECT idEscolaridade
                     FROM tbformacao 
                    WHERE idEscolaridade <> 12 
                      AND idPessoa = {$idPessoa} 
                 ORDER BY idEscolaridade desc LIMIT 1";

        $dados = $pessoal->select($select, false);

        if ($dados) {
            # Pega a maior escolaridade
            $maior = maiorValor([$idEscolaridade, $dados['idEscolaridade']]);

            # Retorna a maior escolaridade registrada
            return $maior;
        } else {
            return $idEscolaridade;
        }
    }

    ###########################################################

    public function exibeBotaoUpload($idFormacao) {
        /**
         * Exibe um botão de upload
         * 
         * @param $idFormacao integer null O id 
         * 
         * @syntax $formacao->exibeBotaoUpload($idFormacao);
         */
        # Verifica se tem id
        if (empty($idFormacao)) {
            return null;
        } else {
            # Exibe o botão
            $link = new Link(null, "?fase=upload&id={$idFormacao}", "Upload o certificado / diploma do curso");
            $link->set_imagem(PASTA_FIGURAS . "upload.png", 20, 20);
            #$link->set_target("_blank");
            $link->show();
        }
    }

    ###########################################################

    public function exibeCertificado($idFormacao) {
        /**
         * Exibe um link para exibir o pdf do certificado
         * 
         * @param $idFormacao integer null O id
         * 
         * @syntax $formacao->exibeCertificado($idFormacao);
         */
        # Verifica se tem id
        if (empty($idFormacao)) {
            return null;
        } else {

            # Monta o arquivo
            $arquivo = PASTA_CERTIFICADO . $idFormacao . ".pdf";

            # Verifica se ele existe
            if (file_exists($arquivo)) {

                # Monta o link
                $link = new Link(null, $arquivo, "Exibe o certificado / diploma do curso");
                $link->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
                $link->set_target("_blank");
                $link->show();
            } else {
                label("Sem<br/>Comprovação", "alert");
            }
        }
    }

###########################################################

    function exibeMarcador($id) {

        /**
         * Fornece Detalhes do curso
         */
        # Verifica se tem id
        if (empty($id)) {
            return null;
        } else {
            # Pega os dados
            $dados = $this->get_dados($id);

            # Marcador 1
            if (!empty($dados['marcador1'])) {
                p($this->get_marcador($dados['marcador1']), "pNota");
            }

            # Marcador 2
            if (!empty($dados['marcador2'])) {
                p($this->get_marcador($dados['marcador2']), "pNota");
            }

            # Marcador 3
            if (!empty($dados['marcador3'])) {
                p($this->get_marcador($dados['marcador3']), "pNota");
            }

            # Marcador 4
            if (!empty($dados['marcador4'])) {
                p($this->get_marcador($dados['marcador4']), "pNota");
            }
        }
    }

    ###########################################################

    function get_arrayMarcadores() {
        /**
         * Fornece um array com os marcadores
         */
        $pessoal = new Pessoal();
        $array = $pessoal->select("SELECT * FROM tbformacaomarcador");
        return $array;
    }

    ###########################################################

    function get_marcador($id = null) {
        /**
         * Fornece um array com os marcadores
         */
        $arrayMarcadores = $this->get_arrayMarcadores();

        foreach ($arrayMarcadores as $item) {
            if ($item[0] == $id) {
                return $item[1];
            }
        }
    }

    ###########################################################

    function temPetec($idServidor, $ano) {
        /**
         * Fornece um array com os marcadores
         */
        # id do Marcador
        $idMarcador = 4;    // Petec
        
        # Verifica se tem id
        if (empty($idServidor)) {
            return false;
        } else {
            # Passa o idservidor para idPessoa
            $pessoal = new Pessoal();
            $idPessoa = $pessoal->get_idPessoa($idServidor);

            # Verifica o Ano
            if (empty($ano)) {
                $ano = date(Y);
            }

            # Select
            $select = "SELECT *
                         FROM tbformacao
                        WHERE anoTerm = '{$ano}' 
                          AND (marcador1 = {$idMarcador} OR marcador2 = {$idMarcador} OR marcador3 = {$idMarcador} OR marcador4 = {$idMarcador})  
                          AND idPessoa = {$idPessoa}";

            $result = $pessoal->select($select);
            $quantidade = $pessoal->count($select);

            if ($quantidade == 0) {
                return false;
            } else {
                return true;
            }
        }
    }

    ###########################################################
}
