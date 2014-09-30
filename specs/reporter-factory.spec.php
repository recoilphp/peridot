<?php
use Peridot\Configuration;
use Peridot\Core\Suite;
use Peridot\Reporter\AnonymousReporter;
use Peridot\Reporter\ReporterFactory;
use Peridot\Reporter\SpecReporter;
use Peridot\Runner\Runner;

describe('ReporterFactory', function() {

    beforeEach(function() {
        $configuration = new Configuration();
        $runner = new Runner(new Suite("test", function() {}));
        $output = new Symfony\Component\Console\Output\NullOutput();
        $this->factory = new ReporterFactory($configuration, $runner, $output);
    });

    describe('->create()', function() {
        context("using a valid reporter name", function() {
            it("should return an instance of the named reporter", function() {
                $reporter = $this->factory->create('spec');
                assert($reporter instanceof SpecReporter, "should create SpecReporter");
            });

            it("should return an anonymous reporter if callable used", function() {
                $this->factory->register('spec2', 'desc', function($runner, $output) {});
                $reporter = $this->factory->create('spec2');
                assert($reporter instanceof AnonymousReporter, "should create AnonymousReporter");
            });
        });

        context("using a valid name with an invalid factory", function() {
            it("should throw an exception", function() {
                $this->factory->register('nope', 'doesnt work', 'Not\A\Class');
                $exception = null;
                try {
                    $this->factory->create('nope');
                } catch (RuntimeException $e) {
                    $exception = $e;
                }
                assert(!is_null($exception), 'exception should have been thrown');
            });
        });

        context("using an invalid name", function() {
            it("should throw an exception", function() {
                $exception = null;
                try {
                    $this->factory->create('nope');
                } catch (RuntimeException $e) {
                    $exception = $e;
                }
                assert(!is_null($exception), 'exception should have been thrown');
            });
        });
    });

    describe('->getReporters()', function() {
        it("should return an array of reporter information", function() {
            $reporters = $this->factory->getReporters();
            assert(isset($reporters['spec']['description']), 'reporter should have description');
            assert(isset($reporters['spec']['factory']), 'reporter should have factory');
        });
    });

    describe('->register()', function() {
       context('using a class', function() {
          it('should add named reporter to list of reporters', function() {
              $this->factory->register('spec2', 'even speccier', 'Peridot\Reporter\SpecReporter');
              $reporters = $this->factory->getReporters();
              assert(isset($reporters['spec2']['description']), 'reporter should have description');
              assert(isset($reporters['spec2']['factory']), 'reporter should have factory');
          });
       });
    });

});
