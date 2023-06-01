<?php

/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */
# Servidor logado 
$idUsuario = null;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario, [1, 2, 12]);

if ($acesso) {
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();
    $page->iniciaPagina();

    ######

    $select = 'SELECT tbpessoa.nome,
                      tbservidor.dtAdmissao,
                      tbpessoa.sexo,
                      tbpessoa.dtNasc,
                      date_format(tbpessoa.dtNasc,"%d/%m/%Y")
                 FROM tbservidor JOIN tbpessoa USING (idPessoa)
                                 JOIN tbperfil USING (idPerfil)      
                                 JOIN tbdocumentacao USING (idPessoa)
                WHERE tbservidor.situacao = 1
                  AND tbperfil.tipo <> "Outros"
                  AND (idCargo = 128 OR idCargo = 129)
             ORDER BY tbpessoa.dtNasc';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Professores Ativos');
    $relatorio->set_subtitulo('Ordenado por Idade Decrescente');
    $relatorio->set_label(['Nome', 'Admissão', 'Sexo', 'Nascimento', 'Idade']);
    $relatorio->set_width([35, 15, 10, 20, 10]);
    $relatorio->set_align(["left", "center", "center", "center"]);
    $relatorio->set_funcao([null, "date_to_php", null, "date_to_php", "idade"]);
    $relatorio->set_conteudo($result);
    $relatorio->show();

    $page->terminaPagina();
}