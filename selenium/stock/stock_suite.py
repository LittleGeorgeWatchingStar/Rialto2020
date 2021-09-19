import unittest
import create_stock_item

def testSuite():
    stock_item_suite = unittest.TestLoader().loadTestsFromModule(create_stock_item)
    test_suite = unittest.TestSuite([
                                     stock_item_suite])
    return test_suite

if __name__ == "__main__":
    test_runner = unittest.TextTestRunner()
    test_suite = testSuite()
    test_runner.run(test_suite)