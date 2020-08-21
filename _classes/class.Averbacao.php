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

        $retorno = getNumDias($dados[0],$dados[1]);

        return $retorno;
    }
}
