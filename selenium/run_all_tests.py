import unittest
from stock import stock_suite

def testSuite():
    stock = stock_suite.testSuite()
    
    suite = unittest.TestSuite([stock]) 
    return suite 
    
if __name__ == "__main__":
    test_runner = unittest.TextTestRunner()
    suite = testSuite()
    test_runner.run(suite)