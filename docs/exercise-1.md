# Exercise 1 — Fundamentos Yii2 (registro de progresso)

Branch: `exercise-1`

## Objetivo
Implementar tela de login + criação de produto usando DDD Lite.

## Decisões iniciais
- Arquitetura: DDD Lite com camadas Domain / Application / Infrastructure.
- Local do Yii2: `src/Infrastructure/Yii`.
- Produto armazena: `id, name, type_id, price, code, created_at`.
- Tipos de produto: tabela `product_type` (criada via migration).
- Campo `code` será calculado no UseCase `CreateProduct`, persistido na entidade Product.

## Checklist
- [x] Criar migrations iniciais (product_type, product)
- [x] Gerar AR via Gii
- [x] Criar Domain/Repository interface
- [x] Implementar Repositories em Infra (usando AR)
- [x] Criar UseCase `CreateProduct`
- [ ] Criar Controller `ProductController` e views (login + create)
- [x] Criar testes unitários para UseCase e Repository
- [ ] Gerar Controller responsavel por view de autenticação
- [ ] Gerar FormModel para a tela de autenticação
- [ ] Tela de login para permitir que o usuário faça login com login e senha
- [ ] Gerar Controller responsável pela criação do produto
- [ ] Gerar FormModel da criação de produto
- [ ] Tela de criação de produto

## Motivações:

#### 1. Criar migrations iniciais (product_type, product)

- A migração `m251127_001237_add_product_type_schema` adiciona uma tabela `product_type` com a intenção de: 
  - padronizar os tipos de produtos disponíveis, para não permitir registros da tabela `product` com tipos duplicados. 
  - evitar que nomes similares, por exemplo `Dress` e `dress`, sejam considerados iguais, assim foi adicionada a coluna `normalized_name`, com restrição **'unique'**.

- Sobre a migração da tabela `product`, a motivação foi:
  - "fortalecer" a consistência dos registros, usando as ferramentas já existentes em bancos de dados do paradigma relacional, como: não permitir cadastrar preço como `NULL`; criar uma chave estrangeira `type_id` referenciando `product_type` com restrição `NOT NULL`
  - promover a regra de negócios para o código do produto que siga o algoritmo `type + 000 + N`. Sendo que column `code` representa o código do produto e pode começar com "0" (**zero**). Assim, decidi usar "VARCHAR", ao invés de um tipo numérico, pois o objetivo é não quebrar o código quando esse iniciar com **zero**.

#### 2. Gerar Active Recrods via Gii

Foram geradas as classes **Active Record** de `Product` e `ProductType` dentro da camada de Infraestrutura.
- Esses Active Records serão usados apenas na Infra para mapeamento e persistência (AR ↔ Entidade do Domain). Eles não devem conter regras de negócio.
- Todas as regras de negócio e invariantes ficam nas entidades de Domain e na lógica de orquestração da camada Application (UseCases). A camada Application usa DTOs na fronteira (entrada/saída) e repositórios do Domain como portas para a Infra.

#### 3. Criar Domain/Repository interface

Foram criados os repositórios de Domain para as entidades Product e ProductType.

- Seguindo DDD, os repositórios representam coleções de entidades, fornecendo métodos de consulta e persistência sem expor detalhes do banco.

- Essa estratégia possibilita que:
  - a camada de domínio permaneça independente do framework de persistência;
  - a futura troca do framework de persistência por outro seja feita sem precisar refatorar o código cliente, apenas alterando a implementação;
  - haja aderência ao princípio "D" do SOLID (Dependency Inversion);
  - a implementação real seja facilmente substituída por mocks em testes de UseCases.

#### 4. Criar UseCase `CreateProduct` e Criar testes unitários para UseCase e Repository

- Definido os DTOs para mapeamento dos objetos de dominio, objetivo:
  - atuar como fronteira explícita do caso de uso (entrada/saída), mantendo o Domain isolado de formatos de transporte e apresentação.

- O caso de uso `CreateProductUseCase` foi criado na aplicação. Objetivo principal:
  - centralizar a orquestração, carregar ProductType, calcular next sequence, gerar code via regras do Domain, criar entidade e persistir.

- Teste de unidade de `CreateProductUseCase`. Com o objetivo de verificar:
  - o comportamento e as invariantes do fluxo (caminho feliz, ProductType inexistente, sequência excedente), usando repositórios fake/mocks para isolar lógica de orquestração do I/O.

> **Benefícios imediatos**: código testável, regras de domínio centralizadas, menor acoplamento ao framework, facilitação de futuras implementações infra (AR/DB) e de estratégias de concorrência/transação.

#### 5. Implementar Repositories em Infra (usando AR)

As interfaces foram implementadas em infra, usando os Active Records com mapeamento para entidades de domínio (e vice-versa).