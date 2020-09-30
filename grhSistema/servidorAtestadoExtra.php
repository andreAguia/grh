<?php

/*
 * Rotina Extra de Validação
 * 
 */


/*
 * Verifica se data é anterior a admissão
 */

$dtInicial = $campoValor[0];
$numDias = $campoValor[1];
$idServidor = $campoValor[7];

$pessoal = new Pessoal();
$dtAdmissao = date_to_bd($pessoal->get_dtAdmissao($idServidor));

if (($dtInicial < $dtAdmissao) AND (!empty($dtInicial))) {
    $msgErro .= 'Você não pode ter atestado antes de ser admitido!\nA data está errada!';
    $erro = 1;
}

/*
 *  Limpa campo parentesco quando o tipo for proprio
 */

$tipo = $campoValor[2];
$parentesco = $campoValor[3];

if ($tipo == "Próprio") {
    $campoValor[3] = null;
}

/*
 *  Exige preenchimento parentesto quando campo tipo for acompanhante
 */

if (($tipo == "Acompanhante") AND (is_null($parentesco = $campoValor[3]))) {
    $msgErro .= 'Deve-se preencher o campo parentesco quando o tipo for acompanhante!';
    $erro = 1;
}

/*
 *  Verifica se já tem outro afastamento nesse período
 */
if (!empty($dtInicial) AND!empty($numDias)) {
    $verifica = new VerificaAfastamentos($idServidor);
    $verifica->setPeriodo(date_to_php($dtInicial), addDias(date_to_php($dtInicial), $numDias));
    $verifica->setIsento("tbatestado", $id);

    if ($verifica->verifica()) {
        $erro = 1;
        $msgErro .= 'Já existe um(a) ' . $verifica->getAfastamento() . ' (' . $verifica->getDetalhe() . ') nesse período!\n';
    }
}