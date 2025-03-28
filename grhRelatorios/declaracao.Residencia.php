<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Inicia as variáveis que receberão as sessions
$idUsuario = null;
$idServidorPesquisado = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();
    
    # Servidor
    $idPessoa = $pessoal->get_idPessoa($idServidorPesquisado);
    $nomeServidor = $pessoal->get_nome($idServidorPesquisado);
    $idFuncional = $pessoal->get_idFuncional($idServidorPesquisado);
    $matricula = $pessoal->get_matricula($idServidorPesquisado);
    $lotacao = $pessoal->get_lotacao($idServidorPesquisado);
    $dtAdmin = $pessoal->get_dtAdmissao($idServidorPesquisado);
    $cargoEfetivo = $pessoal->get_cargoCompleto($idServidorPesquisado);
    $idPerfil = $pessoal->get_idPerfil($idServidorPesquisado);    
    $identidade = $pessoal->get_identidadeSimples($idPessoa);
    $cpf = $pessoal->get_cpf($idPessoa);
    $nacionalidade = strtolower($pessoal->get_nacionalidade($idServidorPesquisado));
    $naturalidade = $pessoal->get_naturalidade($idServidorPesquisado);
    
    

    # Começa uma nova página
    $page = new Page();
    $page->set_title("Declaração de Residência");
    $page->iniciaPagina();
    
    # Monta a Declaração
    $dec = new Declaracao("DECLARAÇÃO DE RESIDÊNCIA");
    
     $dec->set_texto("Eu, <b>" . strtoupper($nomeServidor) . "</b>,"
            . " documento de identidade {$identidade},"
            . " CPF {$cpf}, nacionalidade {$nacionalidade}, natural de {$naturalidade}."
            . " Na falta de documentos para comprovação de residência, DECLARO para os devidos fins, sob"
            . " as penas da Lei, ser residente e domiciliado à:");
     
     $dec->set_texto("Rua: ___________________________________________________________");
     $dec->set_texto("Bairro: ________________________________________________________");
     $dec->set_texto("Cidade: ________________________________________________________");
     $dec->set_texto("UF: __________________ CEP: ____________________________________");
     $dec->set_texto("Telefone: (_____)______________________________________________ ");
     $dec->set_texto("Celular: (_____)________________________________________________");
     $dec->set_texto("E-mail: ________________________________________________________");
     
     $dec->set_texto("Declaro ainda, estar ciente de que se comprovadamente falsa a declaração,"
        . " estar sujeito às sanções civis, administrativas e criminais previstas na legislação aplicável.");
     
    $dec->set_linhaAssinatura(true);
    $dec->set_saltoAssinatura(2);

    # De quem assina
    $dec->set_origemNome($nomeServidor);
    $dec->set_origemIdFuncional($idFuncional);
    $dec->set_origemSetor("");
    $dec->set_origemDescricao("");

    $dec->show();

    # Grava o log da visualização do relatório
    $data = date("Y-m-d H:i:s");
    $atividades = 'Visualizou a declaração eleitoral de rendimentos';
    $tipoLog = 4;
    $intra->registraLog($idUsuario, $data, $atividades, null, null, $tipoLog, $idServidorPesquisado);

    $page->terminaPagina();
}