import React from 'react';
import { Items } from '@/interfaces';
import { Link } from '@inertiajs/inertia-react';

interface PaginationProps {
  items: Items;
}

const Pagination: React.FC<PaginationProps> = ({ items }) => {
  if (!items)
    return

  const { current_page, last_page, path, per_page } = items;

  // Calculate the range of page numbers to display
  const startPage = Math.max(current_page - 2, 1);
  const endPage = Math.min(startPage + 4, last_page);

  // Generate an array of page numbers within the range
  const pageNumbers = Array.from({ length: endPage - startPage + 1 }, (_, index) => startPage + index);
  const pageUrl = ''

  return (
    <div className="flex justify-center mt-5">
      <nav className="flex">
        <ul className="xs:mt-0 mt-2 inline-flex items-center -space-x-px">
          {/* Go back button */}
          <li>
            {current_page > 1 ? (
              <Link href={`${pageUrl}?page=${startPage}`}
                type="button"
                className="ml-0 rounded-l-lg border border-gray-300 bg-white py-2 px-3 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white inline-flex"
                onClick={() => {
                  // Handle going back to the previous page
                  // For example, navigate to `${path}?page=${current_page - 1}`
                }}
              >
                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true" className="h-5 w-5" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Go back
              </Link>
            ) : (
              <button
                type="button"
                className="ml-0 rounded-l-lg border border-gray-300 bg-white py-2 px-3 leading-tight text-gray-500 opacity-50 cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 inline-flex"
                disabled
              >
                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 20 20" aria-hidden="true" className="h-5 w-5" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Go back
              </button>
            )}
          </li>

          {/* Page Numbers */}
          {pageNumbers.map((pageNumber) => (
            <li key={pageNumber}>
              <Link href={`${pageUrl}?page=${pageNumber}`}
                type="button"
                className={`w-12 border border-gray-300 ${pageNumber === current_page
                  ? 'py-2 leading-tight bg-cyan-50 text-cyan-600 hover:bg-cyan-100 hover:text-cyan-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white'
                  : 'py-2 leading-tight bg-white text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white'
                  }`}
                onClick={() => {
                  // Handle clicking on a specific page number
                }}
              >
                {pageNumber}
              </Link>
            </li>
          ))}

          {/* Go forward button */}
          <li>
            {current_page < last_page ? (
              <Link href={`${path}?page=${current_page + 1}`}
                className="mr-0 rounded-r-lg border border-gray-300 bg-white py-2 px-3 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white inline-flex"
              >
                Go forward
                <svg
                  stroke="currentColor"
                  fill="currentColor"
                  strokeWidth="0"
                  viewBox="0 0 20 20"
                  aria-hidden="true"
                  className="h-5 w-5"
                  height="1em"
                  width="1em"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    fillRule="evenodd"
                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                    clipRule="evenodd"
                  ></path>
                </svg>
              </Link>
            ) : (
              <button
                type="button"
                className="opacity-50 cursor-normal rounded-r-lg border border-gray-300 bg-white py-2 px-3 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white inline-flex"
                disabled
              >
                Go forward
                <svg
                  stroke="currentColor"
                  fill="currentColor"
                  strokeWidth="0"
                  viewBox="0 0 20 20"
                  aria-hidden="true"
                  className="h-5 w-5"
                  height="1em"
                  width="1em"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    fillRule="evenodd"
                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                    clipRule="evenodd"
                  ></path>
                </svg>
              </button>
            )}
          </li>
        </ul>
      </nav>
    </div>
  )
};

export default Pagination;
