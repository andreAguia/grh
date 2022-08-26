<?php

class Acumulacao {

    /**
     * Abriga as várias rotina referentes a acumulação de cargos públicos de um servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $idAcumulacao integer null O id da acumulação
     */
##############################################################

    public function get_dados($idAcumulacao) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $idAcumulacao integer null O id da acumulação
         * 
         * @syntax $acumulacao->get_dados([$idAcumulacao]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (vazio($idAcumulacao)) {
            alert("É necessário informar o id da Acumulação.");
            return;
        }

        # Pega os dados
        $select = "SELECT * 
                     FROM tbacumulacao
                    WHERE idAcumulacao = {$idAcumulacao}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row;
    }

##############################################################

    public function get_resultado($idAcumulacao) {

        /**
         * Informa o resultado final de uma acumulação
         * 
         * @param $idAcumulacao integer null O id da acumulação
         * 
         * @syntax $acumulacao->get_resultado([$idAcumulacao]);
         */
        # Inicia a variável de retorno
        $retorno = null;
        $recurso = null;

        # Pega os dados
        $dados = $this->get_dados($idAcumulacao);

        # Joga os resultados nas variáveis
        $resultado = $dados["resultado"];
        $resultado1 = $dados["resultado1"];
        $resultado2 = $dados["resultado2"];
        $resultado3 = $dados["resultado3"];
        $motivo = $dados["motivoSaida"];

        # Verifica se é nulo ou aposentadoria
        # Quando a saída é aposentadoria o sitema continua a exibir ilícito pois o vínculo ainda existe
        # Já que o servidor ainda recebe pelo outro vínculo
        # ---
        # Quando A saída não é de Aposentadoria, o ilícito deixa de ser exibido, pois o vínculo foi desfeito
        # Dessa forma entende-se que o servidor optou pela Uenf
        #---
        if ($motivo == null OR $motivo == 3 OR $motivo == 4 OR $motivo == 5 OR $motivo == 6) {
            # Verifica o primeiro resultado
            if (!vazio($resultado)) {
                $retorno = $resultado;
            }

            # Verifica o primeiro recurso
            if (!vazio($resultado1)) {
                $retorno = $resultado1;
                $recurso = "(1° recurso)";
            }

            # Verifica o segundo recurso
            if (!vazio($resultado2)) {
                $retorno = $resultado2;
                $recurso = "(2° recurso)";
            }

            # Verifica o último recurso
            if (!vazio($resultado3)) {
                $retorno = $resultado3;
                $recurso = "(3° recurso)";
            }

            # Trata o retorno
            if ($retorno == 1) {
                $retorno = "<span class='label success'>Lícito</span>";
            } elseif ($retorno == 2) {
                $retorno = "<span class='label alert'>Ilícito</span>";
            } else {
                $retorno = "---";
            }

            echo $retorno;
            p($recurso, "pgetCargo");
        }else{
            echo "---";
        }
    }

##############################################################

    public function exibePublicacao($idAcumulacao) {

        /**
         * Informe os dados da Publicação do resultado FINAL
         * 
         * @param $idAcumulacao integer null O id da acumulação
         * 
         * @syntax $acumulacao->exibePublicacao([$idAcumulacao]);
         */
        # Pega os dados
        $dados = $this->get_dados($idAcumulacao);

        # Inicia a variável de retorno
        $publicacao = null;
        $pagina = null;
        $recurso = null;

        # Inicial
        $publicacao = $dados["dtPublicacao"];
        $pagina = trataNulo($dados["pgPublicacao"]);

        # Verifica o primeiro recurso
        if (!vazio($dados["resultado1"])) {
            $publicacao = $dados["dtPublicacao1"];
            $pagina = trataNulo($dados["pgPublicacao1"]);
            $recurso = "(1° recurso)";
        }

        # Verifica o segundo recurso
        if (!vazio($dados["resultado2"])) {
            $publicacao = $dados["dtPublicacao2"];
            $pagina = trataNulo($dados["pgPublicacao2"]);
            $recurso = "(2° recurso)";
        }

        # Verifica o último recurso
        if (!vazio($dados["resultado3"])) {
            $publicacao = $dados["dtPublicacao3"];
            $pagina = trataNulo($dados["pgPublicacao3"]);
            $recurso = "(3° recurso)";
        }

        # Retorno
        if (empty($publicacao)) {
            pLista("---");
        } else {
            pLista(date_to_php($publicacao), "pag: " . trataNulo($pagina));
        }

        if (!empty($recurso)) {
            pLista(null, $recurso);
        }
    }

##############################################################

    public function exibeProcesso($idAcumulacao) {

        /**
         * Informe os dados do processo de uma solicitação de Acumulação
         * 
         * @param $idAcumulacao integer null O id da acumulação
         * 
         * @syntax $acumulacao->exibeProcesso([$idAcumulacao]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = "SELECT processo, 
                          dtProcesso
                     FROM tbacumulacao
                    WHERE idAcumulacao = {$idAcumulacao}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        if (empty($row["processo"])) {
            pLista("---");
        } else {
            pLista($row["processo"], date_to_php($row["dtProcesso"]));
        }
    }

##############################################################

    public function exibeDadosOutroVinculo($idAcumulacao) {

        /**
         * Informe os dados do processo de uma solicitação de Acumulação
         * 
         * @param $idAcumulacao integer null O id da acumulação
         * 
         * @syntax $acumulacao->exibeProcesso([$idAcumulacao]);
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = "SELECT instituicao,
                          cargo,                                     
                          matricula,
                          dtAdmissao,
                          dtSaida,
                          motivoSaida
                     FROM tbacumulacao
                    WHERE idAcumulacao = {$idAcumulacao}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        if (empty($row["instituicao"])) {
            return null;
        } else {
            # Variáveis
            $linha4 = null;

            if (!empty($row['dtAdmissao'])) {
                $linha4 .= "Admissão: " . date_to_php($row['dtAdmissao']);
            }

            if (!empty($row['dtSaida'])) {
                $linha4 .= " / Saída: " . date_to_php($row['dtSaida']);
                $linha4 .= "<br/><span class='label warning'>" . $pessoal->get_motivoNome($row['motivoSaida']) . "</span>";
            }

            pLista(
                    $row["instituicao"],
                    $row["cargo"],
                    "Matrícula: {$row['matricula']}",
                    $linha4
            );
        }
    }

##############################################################

    public function exibeDadosUenf($idServidor) {

        /**
         * Informe os dados do Servidor
         * 
         * @param $idServidor integer null O $idServidor
         * 
         * @syntax $acumulacao->exibeDadosUenf([$idAcumulacao]);
         */
        # Joga o valor informado para a variável da classe
        if (vazio($idServidor)) {
            return null;
        } else {
            $pessoal = new Pessoal();
            pLista(
                    $pessoal->get_lotacao($idServidor),
                    $pessoal->get_cargo($idServidor),
                    "Matrícula: {$pessoal->get_matricula($idServidor)}",
                    "Admissão: {$pessoal->get_dtAdmissao($idServidor)}"
            );
        }
    }

##############################################################
}
