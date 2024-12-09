package main

import (
	"fmt"
	"os"
	"strconv"
	"strings"
)

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		fileContentsSplit := strings.Split(string(fileContents), "")
		fileContentsInt := make([]int, len(fileContentsSplit))
		diskSize := 0
		for index, value := range fileContentsSplit {
			fileContentsInt[index], _ = strconv.Atoi(value)
			diskSize += fileContentsInt[index]
		}
		//int for file id, -1 for free space
		var fileSystemContents = make([]int, diskSize)
		var cursorIsOnFile = true
		var fileSystemPosition = 0
		var fileIndex = -1
		var fileSizes = make([]int, len(fileContents)/2+1)
		for _, value := range fileContentsInt {
			if cursorIsOnFile {
				fileIndex++
				fileSizes[fileIndex] = value
			}
			nextFileStart := fileSystemPosition + value
			for ; fileSystemPosition < nextFileStart; fileSystemPosition++ {
				if cursorIsOnFile {
					fileSystemContents[fileSystemPosition] = fileIndex
				} else {
					fileSystemContents[fileSystemPosition] = -1 //free space
				}
			}
			cursorIsOnFile = !cursorIsOnFile
		}
		lastKnownFileEndPosition := len(fileSystemContents) - 1
		for {
			lastKnownFileEndPosition = defragOneFile(lastKnownFileEndPosition, &fileSystemContents, &fileSizes)
			if lastKnownFileEndPosition <= 0 {
				break
			}
		}
		var total = 0
		for index, value := range fileSystemContents {
			if value != -1 {
				total += index * value
			}
		}
		println(total)
	}
}
func defragOneFile(lastKnownFileEndPosition int, diskContents *[]int, fileSizes *[]int) int {

	var actualFileEnd = -1
	for index := lastKnownFileEndPosition; index > 0; index-- {
		if (*diskContents)[index] == -1 {
			continue
		}
		actualFileEnd = index
		break
	}
	if actualFileEnd == -1 {
		return -1
	}
	var fileId = (*diskContents)[actualFileEnd]
	var fileSize = (*fileSizes)[fileId]
	var freeSpaceStart = -1
scanDiskForFreeSpace:
	for diskIndex := 0; diskIndex < len(*diskContents); diskIndex++ {
		for fileIndex := diskIndex; fileIndex < min(diskIndex+fileSize, len(*diskContents)-1); fileIndex++ {
			if (*diskContents)[fileIndex] != -1 {
				continue scanDiskForFreeSpace
			}
		}
		freeSpaceStart = diskIndex
		break
	}
	var fileStart = actualFileEnd - fileSize + 1
	if freeSpaceStart == -1 || freeSpaceStart > fileStart {
		return fileStart - 1
	}
	for index := fileStart; index <= actualFileEnd; index++ {
		(*diskContents)[index] = -1
	}
	for index := freeSpaceStart; index <= min(freeSpaceStart+fileSize-1, len(*diskContents)-1); index++ {
		(*diskContents)[index] = fileId
	}

	return fileStart - 1

}
