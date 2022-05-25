<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario            = null;              # Servidor logado
$idServidorPesquisado = null; # Servidor Editado na pesquisa do sistema do GRH
# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Servidor
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional  = $pessoal->get_idFuncional($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado, false);
    $sexo         = $pessoal->get_sexo($idServidorPesquisado);
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);
    
    if($idPerfil == 2){
        $cargoEfetivo = "exercendo a função equivalente ao {$cargoEfetivo}";
    }else{
        $cargoEfetivo = "ocupante do cargo de {$cargoEfetivo}";
    }

    # Altera parte do texto de acordo com o sexo (gênero) do servidor
    if ($sexo == "Masculino") {
        $texto1 = "o servidor";
    } else {
        $texto1 = "a servidora";
    }

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    # Monta a Declaração
    $dec = new Declaracao();
    $dec->set_declaracaoNome("DECLARAÇÃO DE RESPONSABILIDADE");
    #$dec->set_carimboCnpj(true);
    #$dec->set_assinatura(true);
    
    $dec->set_origemNome($nomeServidor);
    $dec->set_origemSetor(null);
    $dec->set_origemIdFuncional($idFuncional);
    $dec->set_origemDescricao(null);

    $dec->set_data(null);

    $dec->set_texto("<b>" . strtoupper($nomeServidor) . "</b>, ID funcional nº {$idFuncional}, {$cargoEfetivo}, "
            . "<b>declaro(a)</b> para os devidos fins, especialmente para fins de afastamento eleitoral ora requerido, na forma do "
            . "disposto no inciso IV, do art. 74, do Decreto nº 2.479, de 08 de março de 1979, c/c a Lei Complementar nº 64, de 18 de"
            . " maio de 1990, que se responsabiliza perante a Administração Pública estadual pela indicação do lapso temporal correspondente "
            . "ao período de afastamento do exercício funcional ({$cargoEfetivo}) que lhe seja aplicável, prazo que está em consonância com o que "
            . "preceitua a legislação eleitoral, consideradas suas circunstâncias funcionais. Do mesmo modo, afirma ter ciência de que "
            . "a cessação do afastamento eleitoral ocorre de forma automática na hipótese de indeferimento do pedido de registro da candidatura, "
            . "exigindo-se, portanto, o retorno imediato do servidor para o exercício de suas funções, sob pena de aplicação de falta e caracterização "
            . "de abandono de cargo. Declara, igualmente, ter conhecimento de que a ulterior identificação de qualquer irregularidade nas informações ora "
            . "prestadas ensejará a adoção das medidas administrativas necessárias à apuração dos fatos e eventual cominação das sanções disciplinares cabíveis.");

    $dec->set_saltoAssinatura(6);    
    $dec->show();

    # Grava o log da visualização do relatório
    $data       = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração eleitoral de responsabilidade (termo)';
    $tipoLog    = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}