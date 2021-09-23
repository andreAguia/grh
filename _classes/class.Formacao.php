<?php

class Formacao {

    /**
     * Abriga as várias rotina do Cadastro de Formação Escolar do servidor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    ###########################################################

    function exibeCurso($idFormacao) {

        /**
         * fornece Detalhes do curso
         */
        # Pega os dados
        $select = "SELECT habilitacao,
                          instEnsino,
                          anoTerm
                     FROM tbformacao
                    WHERE idFormacao = $idFormacao";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        pLista(
                $dados['habilitacao'],
                $dados['instEnsino'],
                $dados['anoTerm']
        );
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
        $select = "SELECT idEscolaridade FROM tbformacao WHERE idEscolaridade <> 12 AND idPessoa = $idPessoa ORDER BY idEscolaridade desc LIMIT 1";
        $dados = $pessoal->select($select, false);
        
        if(empty($dados[0])){
            $dados[0] = 0;
        }

        # Pega a maior escolaridade
        $maior = maiorValor([$idEscolaridade,$dados[0]]);
        
        # Retorna a maior escolaridade registrada
        return $maior;
    }

}
