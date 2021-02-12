<?php

/*
 * Rotina Extra de Validação
 * 
 */

$dtInicial = $campoValor[0];
$numDias = $campoValor[1];
$idServidor = $campoValor[5];

$pessoal = new Pessoal();

/*
 *  Verifica se a data Inicial é anterior a data de admissão
 */
$dtAdmissao = $pessoal->get_dtAdmissao($idServidor);
$dtAdmissao = date_to_bd($dtAdmissao);
if ($dtInicial < $dtAdmissao) {
    $erro = 1;
    $msgErro .= 'A data Inicial não pode ser antes de ser admitido!\n A data está errada!';
}

/*
 *  Verifica se a data Inicial é posterior a data de saida
 */
$dtSaida = $pessoal->get_dtSaida($idServidor);

# Se tiver data de saida
if (!is_null($dtSaida)) {
    $dtSaida = date_to_bd($dtSaida);
    if ($dtInicial > $dtSaida) {
        $erro = 1;
        $msgErro .= 'A data Inicial não pode ser DEPOIS da saida da UENF!\n';
    }
}

/*
 *  Verifica se já tem outro afastamento nesse período
 */
if (!empty($dtInicial) AND!empty($numDias)) {
    $verifica = new VerificaAfastamentos($idServidor);
    $verifica->setPeriodo(date_to_php($dtInicial), addDias(date_to_php($dtInicial), $numDias));
    $verifica->setIsento("tbtrabalhotre", $id);

    if ($verifica->verifica()) {
        # A pedido de Gustavo retirei do "gesso" as férias e a licença prêmio da verificação
        # para efeito do cadastro de dias trabalhados para o tre
        if ($verifica->getAfastamento() <> "Férias" AND $verifica->getDetalhe() <> "Licença Prêmio") {
            $erro = 1;
            $msgErro .= 'Já existe um(a) ' . $verifica->getAfastamento() . ' (' . $verifica->getDetalhe() . ') nesse período!\n';
        }
    }
}
