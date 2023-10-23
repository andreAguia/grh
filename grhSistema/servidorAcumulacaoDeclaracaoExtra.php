<?php

/*
 * Rotina Extra de Validação * 
 */


/*
 * Verifica se data é anterior a admissão
 */

# Classe pessoal
$pessoal = new Pessoal();

$anoReferencia = $campoValor[0];
$idServidor = $campoValor[5];
$anoAdmissao = year($pessoal->get_dtAdmissao($idServidor));


# Se tiver data de saida
if ($anoReferencia < $anoAdmissao) {
    $erro = 1;
    $msgErro .= 'O Ano de referência não pode ser anterior ao ano de admissão do servidor.\n';
}