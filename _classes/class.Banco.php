<?php

class Banco {

    function zeraContaPadrao($idServidor = null) {

        # Verifica se foi informado o id
        if (empty($idServidor)) {
            return null;
        } else {

            # Passa as contas para não padrão
            $sql = "UPDATE tbhistbanco SET padrao = 'n'
                     WHERE idServidor = {$idServidor}";
            
            $pessoal = new Pessoal();
            $pessoal->update($sql);
        }
    }

###########################################################
}
